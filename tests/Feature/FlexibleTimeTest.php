<?php

namespace Tests\Feature;

use App\Http\Controllers\FlexibleTimeController;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\FlexibleTime;
use App\Models\FlexibleTimeLog;
use App\Models\ShiftTime;
use App\Models\ShiftType;
use App\Models\User;
use App\Services\FlexibleTimeService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlexibleTimeTest extends TestCase
{
    protected $connection = 'test_sqlite';

    protected FlexibleTimeService $service;

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
        Storage::fake('public');

        $this->buildSchema();
        $this->service = app(FlexibleTimeService::class);
    }

    protected function buildSchema()
    {
        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('branches', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->double('tolerance')->nullable();
            $table->integer('parent_branch')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_types', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('period_type')->nullable();
            $table->timestamps();
        });

        Schema::create('shift_types', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('branch_id')->nullable();
            $table->boolean('is_shift')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shift_times', function ($table) {
            $table->increments('id');
            $table->integer('shift_type_id');
            $table->string('days')->nullable();
            $table->boolean('is_working')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('shift_type_id')->nullable();
            $table->integer('managed_by')->nullable();
            $table->boolean('is_shift')->default(false);
            $table->double('salary')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('flexible_times', function ($table) {
            $table->id();
            $table->integer('employee_id');
            $table->date('date');
            $table->time('normal_start_time')->nullable();
            $table->time('normal_end_time')->nullable();
            $table->time('requested_start_time');
            $table->time('requested_end_time')->nullable();
            $table->time('approved_start_time')->nullable();
            $table->time('approved_end_time')->nullable();
            $table->text('reason')->nullable();
            $table->string('document')->nullable();
            $table->string('status')->default('pending');
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->integer('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->integer('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'date']);
        });

        Schema::create('flexible_time_logs', function ($table) {
            $table->id();
            $table->integer('flexible_time_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->integer('changed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Reserved ids: 1 = company, 2 = hr (mirrors the production rules).
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Company', 'type' => 'company', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'HR', 'type' => 'hr', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    protected function makeHeadOfficeFulltimeEmployee(string $name, ?int $managedBy = null, ?int $userId = null): Employee
    {
        $branch = Branch::create(['name' => 'Head Office']);

        $type = new EmployeeType();
        $type->name = 'Full Time';
        $type->type = 'Fixed';
        $type->save();

        $shiftType = ShiftType::create(['name' => 'Full Time']);
        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
            ShiftTime::create([
                'shift_type_id' => $shiftType->id,
                'days' => $day,
                'is_working' => 1,
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
            ]);
        }

        return Employee::create([
            'name' => $name,
            'user_id' => $userId,
            'type_id' => $type->id,
            'branch_id' => $branch->id,
            'shift_type_id' => $shiftType->id,
            'managed_by' => $managedBy,
            'is_active' => 1,
        ]);
    }

    protected function makeUser(string $name, string $type, ?int $employeeId = null): User
    {
        $user = User::create(['name' => $name, 'type' => $type, 'is_active' => 1]);

        if ($employeeId !== null) {
            Employee::where('id', $employeeId)->update(['user_id' => $user->id]);
            $user->setRelation('employee', Employee::find($employeeId));
        } else {
            $user->setRelation('employee', null);
        }

        return $user;
    }

    // ------------------------------------------------------------------
    // Eligibility
    // ------------------------------------------------------------------

    public function test_head_office_fulltime_employee_is_eligible(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');

        $this->assertTrue($this->service->isEligible($employee));
    }

    public function test_employee_outside_head_office_is_not_eligible(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $employee->branch_id = Branch::create(['name' => 'Branch Surabaya'])->id;
        $employee->save();

        $this->assertFalse($this->service->isEligible($employee));
    }

    public function test_daily_worker_is_not_eligible(): void
    {
        $branch = Branch::create(['name' => 'Head Office']);

        $type = new EmployeeType();
        $type->name = 'Daily Worker';
        $type->type = 'Daily';
        $type->save();

        $shiftType = ShiftType::create(['name' => 'Daily Worker']);

        $employee = Employee::create([
            'name' => 'Tono',
            'type_id' => $type->id,
            'branch_id' => $branch->id,
            'shift_type_id' => $shiftType->id,
            'is_active' => 1,
        ]);

        $this->assertFalse($this->service->isEligible($employee));
    }

    // ------------------------------------------------------------------
    // Effective schedule
    // ------------------------------------------------------------------

    public function test_effective_schedule_falls_back_to_normal_without_flexible(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $date = '2026-11-05'; // Thursday

        $schedule = $this->service->getEffectiveWorkSchedule($employee->id, $date);

        $this->assertNotNull($schedule);
        $this->assertFalse($schedule['flexible']);
        $this->assertSame('08:00:00', $schedule['start_time']);
        $this->assertSame('16:00:00', $schedule['end_time']);
    }

    public function test_effective_schedule_uses_approved_flexible_time(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $date = '2026-11-05';

        FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'normal_start_time' => '08:00:00',
            'normal_end_time' => '16:00:00',
            'requested_start_time' => '10:00:00',
            'requested_end_time' => '19:00:00',
            'approved_start_time' => '10:00:00',
            'approved_end_time' => '19:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_APPROVED,
            'created_by' => 1,
        ]);

        $schedule = $this->service->getEffectiveWorkSchedule($employee->id, $date);

        $this->assertTrue($schedule['flexible']);
        $this->assertSame('10:00:00', $schedule['start_time']);
        $this->assertSame('19:00:00', $schedule['end_time']);
        $this->assertSame('08:00:00', $schedule['normal_start_time']);
    }

    public function test_pending_flexible_time_is_ignored_by_effective_schedule(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $date = '2026-11-05';

        FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'requested_start_time' => '10:00:00',
            'requested_end_time' => '19:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $schedule = $this->service->getEffectiveWorkSchedule($employee->id, $date);

        $this->assertFalse($schedule['flexible']);
        $this->assertSame('08:00:00', $schedule['start_time']);
    }

    public function test_apply_to_shift_times_only_overrides_when_approved(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $date = '2026-11-05';

        $shiftTimes = ShiftTime::where('shift_type_id', $employee->shift_type_id)->where('days', 'Thursday')->first();

        FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'requested_start_time' => '09:30:00',
            'requested_end_time' => '18:30:00',
            'approved_start_time' => '09:30:00',
            'approved_end_time' => '18:30:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_APPROVED,
            'created_by' => 1,
        ]);

        $overridden = $this->service->applyToShiftTimes($shiftTimes, $employee->id, $date);

        $this->assertSame('09:30:00', $overridden->start_time);
        $this->assertSame('18:30:00', $overridden->end_time);
    }

    // ------------------------------------------------------------------
    // Validation rules
    // ------------------------------------------------------------------

    public function test_requested_start_cannot_be_earlier_than_normal(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');

        $error = $this->service->validateRequestedStart($employee, '2026-11-05', '07:00');

        $this->assertNotNull($error);
    }

    public function test_requested_start_cannot_exceed_eleven_am(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');

        $error = $this->service->validateRequestedStart($employee, '2026-11-05', '11:30');

        $this->assertNotNull($error);
    }

    public function test_requested_start_within_bounds_is_valid(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');

        $this->assertNull($this->service->validateRequestedStart($employee, '2026-11-05', '08:00'));
        $this->assertNull($this->service->validateRequestedStart($employee, '2026-11-05', '11:00'));
    }

    public function test_requested_end_is_fixed_nine_hours(): void
    {
        $this->assertSame('19:00:00', $this->service->requestedEndFromStart('10:00'));
        $this->assertSame('20:00:00', $this->service->requestedEndFromStart('11:00'));
    }

    // ------------------------------------------------------------------
    // Permissions
    // ------------------------------------------------------------------

    public function test_company_user_one_can_view_all_and_approve(): void
    {
        $boss = $this->makeHeadOfficeFulltimeEmployee('Boss');
        $bossUser = User::find(1);
        $bossUser->setRelation('employee', null);

        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi', $boss->id);
        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $this->assertTrue(FlexibleTime::canViewAll($bossUser));
        $this->assertTrue(FlexibleTime::canApprove($bossUser, $request));
    }

    public function test_direct_supervisor_can_approve_subordinate_request(): void
    {
        $supervisorEmployee = $this->makeHeadOfficeFulltimeEmployee('Supervisor');
        $supervisor = $this->makeUser('Supervisor', 'employee', $supervisorEmployee->id);

        $subordinate = $this->makeHeadOfficeFulltimeEmployee('Budi', $supervisorEmployee->id);
        $request = FlexibleTime::create([
            'employee_id' => $subordinate->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $this->assertTrue(FlexibleTime::canApprove($supervisor, $request));
        $this->assertTrue(FlexibleTime::canView($supervisor, $request));
    }

    public function test_unrelated_employee_cannot_approve(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $other = $this->makeHeadOfficeFulltimeEmployee('Other');
        $otherUser = $this->makeUser('Other', 'employee', $other->id);

        $this->assertFalse(FlexibleTime::canApprove($otherUser, $request));
        $this->assertFalse(FlexibleTime::canView($otherUser, $request));
    }

    public function test_self_approval_is_blocked(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);

        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => $user->id,
        ]);

        $this->assertFalse(FlexibleTime::canApprove($user, $request));
        $this->assertTrue(FlexibleTime::canView($user, $request));
    }

    public function test_hr_user_two_has_read_only_access(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $hr = User::find(2);
        $hr->setRelation('employee', null);

        $this->assertTrue(FlexibleTime::canViewAll($hr));
        $this->assertTrue(FlexibleTime::canView($hr, $request));
        $this->assertFalse(FlexibleTime::canApprove($hr, $request));
        $this->assertTrue(FlexibleTime::canAccessMenu($hr));
    }

    public function test_hr_user_other_than_two_is_fully_blocked(): void
    {
        $supervisorEmployee = $this->makeHeadOfficeFulltimeEmployee('Supervisor');
        $hrEmployee = $this->makeHeadOfficeFulltimeEmployee('HR Staff', $supervisorEmployee->id);

        $request = FlexibleTime::create([
            'employee_id' => $supervisorEmployee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $hr = $this->makeUser('HR Staff', 'hr', $hrEmployee->id);

        $this->assertTrue(FlexibleTime::isHrBlocked($hr));
        $this->assertFalse(FlexibleTime::canView($hr, $request));
        $this->assertFalse(FlexibleTime::canApprove($hr, $request));
        $this->assertFalse(FlexibleTime::canAccessMenu($hr));
    }

    public function test_menu_hidden_for_plain_employee_without_requests_or_subordinates(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);

        // Eligible employee sees the menu (may submit requests).
        $this->assertTrue(FlexibleTime::canAccessMenu($user));

        // Move the employee out of Head Office: no longer eligible, no subordinates.
        $employee->branch_id = Branch::create(['name' => 'Branch Bandung'])->id;
        $employee->save();
        $user->setRelation('employee', $employee->fresh());

        $this->assertFalse(FlexibleTime::canAccessMenu($user->fresh()));
    }

    // ------------------------------------------------------------------
    // Controller: approve / reject / cancel
    // ------------------------------------------------------------------

    public function test_supervisor_approve_marks_request_approved_and_writes_log(): void
    {
        $supervisorEmployee = $this->makeHeadOfficeFulltimeEmployee('Supervisor');
        $supervisor = $this->makeUser('Supervisor', 'employee', $supervisorEmployee->id);

        $subordinate = $this->makeHeadOfficeFulltimeEmployee('Budi', $supervisorEmployee->id);
        $request = FlexibleTime::create([
            'employee_id' => $subordinate->id,
            'date' => '2026-11-05',
            'normal_start_time' => '08:00:00',
            'normal_end_time' => '16:00:00',
            'requested_start_time' => '10:00:00',
            'requested_end_time' => '19:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $this->actingAs($supervisor);

        $controller = app(FlexibleTimeController::class);
        $controller->approve($request->id);

        $fresh = $request->fresh();
        $this->assertSame(FlexibleTime::STATUS_APPROVED, $fresh->status);
        $this->assertSame('10:00:00', $fresh->approved_start_time);
        $this->assertSame('19:00:00', $fresh->approved_end_time);
        $this->assertSame($supervisor->id, (int) $fresh->approved_by);
        $this->assertNotNull($fresh->approved_at);

        $log = FlexibleTimeLog::where('flexible_time_id', $request->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(FlexibleTime::STATUS_PENDING, $log->from_status);
        $this->assertSame(FlexibleTime::STATUS_APPROVED, $log->to_status);

        // Effective schedule for that date now reflects the approval.
        $schedule = $this->service->getEffectiveWorkSchedule($subordinate->id, '2026-11-05');
        $this->assertTrue($schedule['flexible']);
        $this->assertSame('10:00:00', $schedule['start_time']);
    }

    public function test_non_supervisor_cannot_approve_via_controller(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $other = $this->makeHeadOfficeFulltimeEmployee('Other');
        $otherUser = $this->makeUser('Other', 'employee', $other->id);
        $this->actingAs($otherUser);

        $controller = app(FlexibleTimeController::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->approve($request->id);
    }

    public function test_reject_records_reason_and_status(): void
    {
        $supervisorEmployee = $this->makeHeadOfficeFulltimeEmployee('Supervisor');
        $supervisor = $this->makeUser('Supervisor', 'employee', $supervisorEmployee->id);

        $subordinate = $this->makeHeadOfficeFulltimeEmployee('Budi', $supervisorEmployee->id);
        $request = FlexibleTime::create([
            'employee_id' => $subordinate->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $this->actingAs($supervisor);

        $controller = app(FlexibleTimeController::class);
        $controller->reject(
            Request::create('/x', 'POST', ['rejection_reason' => 'Shift coverage needed']),
            $request->id
        );

        $fresh = $request->fresh();
        $this->assertSame(FlexibleTime::STATUS_REJECTED, $fresh->status);
        $this->assertSame('Shift coverage needed', $fresh->rejection_reason);
        $this->assertSame($supervisor->id, (int) $fresh->rejected_by);
    }

    public function test_already_processed_request_cannot_be_approved_again(): void
    {
        $supervisorEmployee = $this->makeHeadOfficeFulltimeEmployee('Supervisor');
        $supervisor = $this->makeUser('Supervisor', 'employee', $supervisorEmployee->id);

        $subordinate = $this->makeHeadOfficeFulltimeEmployee('Budi', $supervisorEmployee->id);
        $request = FlexibleTime::create([
            'employee_id' => $subordinate->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'approved_start_time' => '10:00:00',
            'approved_end_time' => '19:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_APPROVED,
            'created_by' => 1,
        ]);

        $this->actingAs($supervisor);

        $controller = app(FlexibleTimeController::class);
        $response = $controller->approve($request->id);

        $this->assertSame(FlexibleTime::STATUS_APPROVED, $request->fresh()->status);
        $this->assertNotNull(session('error'));
        $this->assertTrue($response->isRedirect());
    }

    public function test_requester_can_cancel_own_pending_request(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);

        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $controller = app(FlexibleTimeController::class);
        $controller->cancel($request->id);

        $fresh = $request->fresh();
        $this->assertSame(FlexibleTime::STATUS_CANCELLED, $fresh->status);
        $this->assertSame($user->id, (int) $fresh->cancelled_by);
        $this->assertNotNull($fresh->cancelled_at);
    }

    public function test_approved_request_cannot_be_cancelled(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);

        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'approved_start_time' => '10:00:00',
            'approved_end_time' => '19:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_APPROVED,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $controller = app(FlexibleTimeController::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->cancel($request->id);
    }

    public function test_other_employee_cannot_cancel_someone_elses_request(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $request = FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => 1,
        ]);

        $other = $this->makeHeadOfficeFulltimeEmployee('Other');
        $otherUser = $this->makeUser('Other', 'employee', $other->id);
        $this->actingAs($otherUser);

        $controller = app(FlexibleTimeController::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->cancel($request->id);
    }

    // ------------------------------------------------------------------
    // Controller: create
    // ------------------------------------------------------------------

    public function test_store_creates_pending_request_with_normal_snapshot(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $controller = app(FlexibleTimeController::class);
        $controller->store(Request::create('/x', 'POST', [
            'date' => '2026-11-05',
            'requested_start_time' => '10:00',
            'reason' => 'Family matter',
        ], [], ['document' => $file]));

        $stored = FlexibleTime::where('employee_id', $employee->id)->first();
        $this->assertNotNull($stored);
        $this->assertSame(FlexibleTime::STATUS_PENDING, $stored->status);
        $this->assertSame('10:00:00', $stored->requested_start_time);
        $this->assertSame('19:00:00', $stored->requested_end_time);
        $this->assertSame('08:00:00', $stored->normal_start_time);
        $this->assertSame('16:00:00', $stored->normal_end_time);
        $this->assertSame($user->id, (int) $stored->created_by);
        $this->assertNotNull($stored->document);
        $this->assertStringContainsString('/storage/uploads/flexible-times/', $stored->document);
        $this->assertNotNull(session('success'));
    }

    public function test_store_requires_document_upload(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);
        $this->actingAs($user);

        $controller = app(FlexibleTimeController::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $controller->store(Request::create('/x', 'POST', [
            'date' => '2026-11-05',
            'requested_start_time' => '10:00',
            'reason' => 'Family matter',
        ]));
    }

    public function test_store_rejects_start_time_beyond_eleven_am(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $controller = app(FlexibleTimeController::class);
        $controller->store(Request::create('/x', 'POST', [
            'date' => '2026-11-05',
            'requested_start_time' => '11:30',
            'reason' => 'Family matter',
        ], [], ['document' => $file]));

        $this->assertSame(0, FlexibleTime::where('employee_id', $employee->id)->count());
        $this->assertNotNull(session('error'));
    }

    public function test_store_rejects_duplicate_active_request_for_same_date(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');
        $user = $this->makeUser('Budi', 'employee', $employee->id);
        $this->actingAs($user);

        FlexibleTime::create([
            'employee_id' => $employee->id,
            'date' => '2026-11-05',
            'requested_start_time' => '10:00:00',
            'reason' => 'Family matter',
            'status' => FlexibleTime::STATUS_PENDING,
            'created_by' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $controller = app(FlexibleTimeController::class);
        $controller->store(Request::create('/x', 'POST', [
            'date' => '2026-11-05',
            'requested_start_time' => '09:00',
            'reason' => 'Another reason',
        ], [], ['document' => $file]));

        $this->assertSame(1, FlexibleTime::where('employee_id', $employee->id)->count());
        $this->assertNotNull(session('error'));
    }

    public function test_ineligible_employee_cannot_open_create_form(): void
    {
        $branch = Branch::create(['name' => 'Branch Surabaya']);

        $type = new EmployeeType();
        $type->name = 'Daily Worker';
        $type->type = 'Daily';
        $type->save();

        $employee = Employee::create([
            'name' => 'Tono',
            'type_id' => $type->id,
            'branch_id' => $branch->id,
            'is_active' => 1,
        ]);
        $user = $this->makeUser('Tono', 'employee', $employee->id);
        $this->actingAs($user);

        $controller = app(FlexibleTimeController::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->create();
    }

    // ------------------------------------------------------------------
    // Scope
    // ------------------------------------------------------------------

    public function test_status_scopes(): void
    {
        $employee = $this->makeHeadOfficeFulltimeEmployee('Budi');

        FlexibleTime::create([
            'employee_id' => $employee->id, 'date' => '2026-11-05',
            'requested_start_time' => '09:00:00', 'reason' => 'a',
            'status' => FlexibleTime::STATUS_PENDING, 'created_by' => 1,
        ]);
        FlexibleTime::create([
            'employee_id' => $employee->id, 'date' => '2026-11-06',
            'requested_start_time' => '09:00:00', 'reason' => 'b',
            'status' => FlexibleTime::STATUS_APPROVED, 'created_by' => 1,
        ]);
        FlexibleTime::create([
            'employee_id' => $employee->id, 'date' => '2026-11-07',
            'requested_start_time' => '09:00:00', 'reason' => 'c',
            'status' => FlexibleTime::STATUS_REJECTED, 'created_by' => 1,
        ]);

        $this->assertSame(1, FlexibleTime::pending()->count());
        $this->assertSame(1, FlexibleTime::approved()->count());
        $this->assertSame(2, FlexibleTime::activeStatuses()->count());
    }
}
