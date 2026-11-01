<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\SalaryChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SalaryChangeRequestTest extends TestCase
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
        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('designations', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('level_id')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('employee_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('designation_id')->nullable();
            $table->integer('shift_type_id')->nullable();
            $table->integer('salary_type')->nullable();
            $table->double('salary')->default(0);
            $table->boolean('is_shift')->default(false);
            $table->integer('employee_type_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('salary_change_requests', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->double('old_salary')->default(0);
            $table->double('new_salary');
            $table->string('source')->default('form');
            $table->string('status')->default('Pending');
            $table->integer('requested_by')->nullable();
            $table->integer('reviewed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function makeUser(string $name, string $type, ?int $designationId = null): User
    {
        $user = User::create(['name' => $name, 'type' => $type, 'is_active' => true]);

        if ($designationId !== null) {
            $employee = Employee::create([
                'name' => $name,
                'user_id' => $user->id,
                'designation_id' => $designationId,
                'salary' => 0,
            ]);
            $user->setRelation('employee', $employee);
        } else {
            $user->setRelation('employee', null);
        }

        return $user;
    }

    public function test_only_designation_four_is_a_reviewer(): void
    {
        $reviewer = $this->makeUser('SM HRD', 'employee', 4);
        $other = $this->makeUser('Staff', 'employee', 5);
        $noEmployee = $this->makeUser('Bot', 'company');

        $this->assertTrue(SalaryChangeRequest::isReviewer($reviewer));
        $this->assertFalse(SalaryChangeRequest::isReviewer($other));
        $this->assertFalse(SalaryChangeRequest::isReviewer($noEmployee));
        $this->assertFalse(SalaryChangeRequest::isReviewer(null));
    }

    public function test_approving_writes_the_new_salary(): void
    {
        $reviewer = $this->makeUser('SM HRD', 'employee', 4);
        $employee = Employee::create(['name' => 'Budi', 'salary' => 3000000]);

        $request = SalaryChangeRequest::create([
            'employee_id' => $employee->id,
            'old_salary' => 3000000,
            'new_salary' => 4500000,
            'status' => SalaryChangeRequest::STATUS_PENDING,
        ]);

        $this->actingAs($reviewer);

        $controller = app(\App\Http\Controllers\SalaryChangeRequestController::class);
        $controller->approve(
            \Illuminate\Http\Request::create('/x', 'POST'),
            $request->id
        );

        $this->assertEquals(4500000, (float) Employee::find($employee->id)->salary);
        $this->assertEquals(SalaryChangeRequest::STATUS_APPROVED, $request->fresh()->status);
    }

    public function test_rejecting_leaves_the_salary_untouched(): void
    {
        $reviewer = $this->makeUser('SM HRD', 'employee', 4);
        $employee = Employee::create(['name' => 'Budi', 'salary' => 3000000]);

        $request = SalaryChangeRequest::create([
            'employee_id' => $employee->id,
            'old_salary' => 3000000,
            'new_salary' => 4500000,
            'status' => SalaryChangeRequest::STATUS_PENDING,
        ]);

        $this->actingAs($reviewer);

        $controller = app(\App\Http\Controllers\SalaryChangeRequestController::class);
        $controller->reject(
            \Illuminate\Http\Request::create('/x', 'POST', ['note' => 'Tidak sesuai-NC']),
            $request->id
        );

        $this->assertEquals(3000000, (float) Employee::find($employee->id)->salary);
        $this->assertEquals(SalaryChangeRequest::STATUS_REJECTED, $request->fresh()->status);
        $this->assertEquals('Tidak sesuai-NC', $request->fresh()->note);
    }

    public function test_non_reviewer_cannot_approve(): void
    {
        $staff = $this->makeUser('Staff', 'employee', 5);
        $employee = Employee::create(['name' => 'Budi', 'salary' => 3000000]);

        $request = SalaryChangeRequest::create([
            'employee_id' => $employee->id,
            'old_salary' => 3000000,
            'new_salary' => 4500000,
            'status' => SalaryChangeRequest::STATUS_PENDING,
        ]);

        $this->actingAs($staff);

        $controller = app(\App\Http\Controllers\SalaryChangeRequestController::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->approve(\Illuminate\Http\Request::create('/x', 'POST'), $request->id);
    }

    public function test_scopes_split_pending_and_reviewed(): void
    {
        $employee = Employee::create(['name' => 'Budi', 'salary' => 1000000]);

        SalaryChangeRequest::create(['employee_id' => $employee->id, 'old_salary' => 1000000, 'new_salary' => 2000000, 'status' => 'Pending']);
        SalaryChangeRequest::create(['employee_id' => $employee->id, 'old_salary' => 2000000, 'new_salary' => 3000000, 'status' => 'Approved']);
        SalaryChangeRequest::create(['employee_id' => $employee->id, 'old_salary' => 3000000, 'new_salary' => 4000000, 'status' => 'Rejected']);

        $this->assertSame(1, SalaryChangeRequest::pending()->count());
        $this->assertSame(2, SalaryChangeRequest::reviewed()->count());
    }
}
