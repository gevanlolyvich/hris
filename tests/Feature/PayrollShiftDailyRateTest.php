<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayrollShiftDailyRateTest extends TestCase
{
    protected $connection = 'test_sqlite';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.' . $this->connection => array_merge(config('database.connections.mysql'), [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'charset' => 'utf8',
                'collation' => null,
                'strict' => false,
                'engine' => null,
            ]),
        ]);

        DB::setDefaultConnection($this->connection);

        $this->buildSchema();
    }

    protected function buildSchema()
    {
        Schema::create('employees', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('shift_type_id')->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->boolean('is_shift')->default(false);
            $table->integer('employee_type_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shift_types', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('shift_times', function ($table) {
            $table->increments('id');
            $table->integer('shift_type_id');
            $table->string('days');
            $table->boolean('is_working')->default(false);
            $table->timestamps();
        });

        // getTotalWorkdays() reads this to subtract public holidays. Empty here, so
        // the divisor stays at the raw Mon-Fri calendar count.
        Schema::create('holidays', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // getPresentDays() aggregates this. Left empty so present days stay 0 in
        // the divisor tests; the rate arithmetic is asserted directly instead.
        Schema::create('attendance_employees', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->date('date');
            $table->string('status')->nullable();
            $table->string('work_hours')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->integer('shift_type_id')->nullable();
            $table->timestamps();
        });
    }

    protected function makeEmployee(array $attrs = []): Employee
    {
        $employee = new Employee();
        $employee->forceFill(array_merge([
            'name' => 'Test',
            'salary' => 2000000,
            'is_shift' => 1,
        ], $attrs));
        $employee->exists = false;
        $employee->setRelation('shift_type', null);
        $employee->setRelation('shiftTimes', collect());

        // Save through the real model so the is_shift/shift_type hook and the
        // calendar divisor both run exactly as they do in payroll.
        $employee->save();

        return $employee;
    }

    public function test_shift_employee_uses_calendar_workdays_not_roster_rows(): void
    {
        $employee = $this->makeEmployee();

        // is_shift = 1 forces shift_type_id to null, so the divisor falls back to
        // the default Monday-Friday working days. October 2026 has 22 of them.
        list($total_work_days, $total_present_days) = $employee->salaryWorkdaysAndPresentDays('10', '2026');

        $this->assertSame(22, $total_work_days, 'October 2026 must resolve to 22 Mon-Fri days.');
        $this->assertSame(0, $total_present_days, 'No attendance rows in this isolated schema.');
    }

    public function test_calendar_divisor_ignores_roster_row_multiplicity(): void
    {
        $employee = $this->makeEmployee();

        // Insert roster rows the way multi-shift does: two rows sharing one date.
        Schema::create('employee_shift_schedules', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->date('date');
            $table->timestamps();
        });
        DB::table('employee_shift_schedules')->insert([
            ['employee_id' => $employee->id, 'date' => '2026-10-01'],
            ['employee_id' => $employee->id, 'date' => '2026-10-01'],
        ]);

        list($total_work_days) = $employee->salaryWorkdaysAndPresentDays('10', '2026');

        $this->assertSame(
            22,
            $total_work_days,
            'Duplicate roster rows on one date must not inflate the divisor.'
        );
    }

    public function test_rate_above_requirement_is_paid_extra(): void
    {
        $employee = $this->makeEmployee();

        // 24 present days over a 22-day requirement -> 1.0909...
        $rate = $employee->payrollRate(24, 22);
        $this->assertEqualsWithDelta(24 / 22, $rate, 0.000001);

        $salary = 4901798;
        $this->assertEqualsWithDelta(5347416, $salary * $rate, 0.01);
    }

    public function test_rate_below_requirement_is_deducted_proportionally(): void
    {
        $employee = $this->makeEmployee();

        $rate = $employee->payrollRate(18, 20);
        $this->assertEqualsWithDelta(0.9, $rate, 0.000001);
        $this->assertEqualsWithDelta(1800000, 2000000 * $rate, 0.01);
    }

    public function test_rate_at_requirement_is_full_salary(): void
    {
        $employee = $this->makeEmployee();

        $this->assertEqualsWithDelta(1.0, $employee->payrollRate(20, 20), 0.000001);
    }

    public function test_rate_never_exceeds_one_for_non_shift(): void
    {
        $employee = $this->makeEmployee(['is_shift' => 0]);

        $this->assertEqualsWithDelta(1.0, $employee->payrollRate(30, 22), 0.000001);
    }

    public function test_rate_returns_one_when_no_work_days_resolve(): void
    {
        $employee = $this->makeEmployee();

        $this->assertEquals(1.0, $employee->payrollRate(0, 0));
    }

    public function test_user_example_two_million_salary_pays_two_point_two_million(): void
    {
        $employee = $this->makeEmployee(['salary' => 2000000]);

        // The user's worked example: 20 required days, 22 attended.
        $rate = $employee->payrollRate(22, 20);
        $this->assertEqualsWithDelta(2200000, 2000000 * $rate, 0.01);
    }
}
