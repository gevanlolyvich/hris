<?php

namespace Tests\Feature;

use App\Utilities\AttendanceLocationResolver;
use App\Models\EmployeeBranchHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceLocationResolverTest extends TestCase
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

    protected function tearDown(): void
    {
        AttendanceLocationResolver::flush();

        parent::tearDown();
    }

    protected function buildSchema()
    {
        Schema::create('branches', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('employees', function ($table) {
            $table->increments('id');
            $table->integer('branch_id')->nullable();
            $table->date('company_doj')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attendance_employees', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('transfers', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->integer('branch_id');
            $table->date('transfer_date');
            $table->timestamps();
        });

        Schema::create('employee_branch_histories', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->integer('branch_id');
            $table->date('effective_date');
            $table->timestamps();
        });
    }

    protected function createBranch(string $name): int
    {
        return DB::table('branches')->insertGetId(['name' => $name]);
    }

    protected function createEmployee(int $branchId): int
    {
        return DB::table('employees')->insertGetId(['branch_id' => $branchId]);
    }

    protected function createEmployeeWithDoj(int $branchId, string $doj): int
    {
        return DB::table('employees')->insertGetId(['branch_id' => $branchId, 'company_doj' => $doj]);
    }

    protected function createAttendance(int $employeeId, string $date): void
    {
        DB::table('attendance_employees')->insert([
            'employee_id' => $employeeId,
            'date'        => $date,
        ]);
    }

    protected function createInitialPlacement(int $employeeId, int $branchId, string $effectiveDate): void
    {
        DB::table('employee_branch_histories')->insert([
            'employee_id'    => $employeeId,
            'branch_id'      => $branchId,
            'effective_date' => $effectiveDate,
        ]);
    }

    protected function createTransfer(int $employeeId, int $branchId, string $date): void
    {
        DB::table('transfers')->insert([
            'employee_id'   => $employeeId,
            'branch_id'     => $branchId,
            'transfer_date' => $date,
        ]);
    }

    public function test_employee_without_transfer_and_without_history_uses_current_branch()
    {
        $branch = $this->createBranch('ALHIJRA');
        $employeeId = $this->createEmployee($branch);

        $this->assertEquals($branch, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-05-15'));
        $this->assertEquals('ALHIJRA', AttendanceLocationResolver::resolveBranchName($employeeId, '2025-05-15'));
    }

    public function test_employee_without_transfer_uses_initial_history_branch_for_all_dates()
    {
        $currentBranch = $this->createBranch('GCR');
        $initialBranch = $this->createBranch('ALHIJRA');

        // employees.branch_id reflects the current branch, but the immutable
        // initial reference lives in employee_branch_histories.
        $employeeId = $this->createEmployee($currentBranch);
        $this->createInitialPlacement($employeeId, $initialBranch, '2024-01-17');

        $this->assertEquals($initialBranch, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-01-17'));
        $this->assertEquals($initialBranch, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-05-15'));
        $this->assertEquals('ALHIJRA', AttendanceLocationResolver::resolveBranchName($employeeId, '2025-05-15'));
    }

    public function test_single_transfer_resolves_old_and_new_branch_by_date()
    {
        $plumpang = $this->createBranch('PLUMPANG');
        $gcr = $this->createBranch('GCR');

        $employeeId = $this->createEmployee($gcr);
        $this->createInitialPlacement($employeeId, $plumpang, '2024-01-17');
        $this->createTransfer($employeeId, $gcr, '2024-07-22');

        $this->assertEquals($plumpang, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-21'));
        $this->assertEquals('PLUMPANG', AttendanceLocationResolver::resolveBranchName($employeeId, '2024-07-21'));

        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-22'));
        $this->assertEquals('GCR', AttendanceLocationResolver::resolveBranchName($employeeId, '2024-07-22'));

        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-05-15'));
    }

    public function test_multiple_transfers_use_latest_effective_transfer()
    {
        $plumpang = $this->createBranch('PLUMPANG');
        $cempakaPutih = $this->createBranch('CEMPAKA PUTIH');
        $gcr = $this->createBranch('GCR');

        $employeeId = $this->createEmployee($gcr);
        $this->createInitialPlacement($employeeId, $plumpang, '2024-01-17');
        $this->createTransfer($employeeId, $cempakaPutih, '2024-07-22');
        $this->createTransfer($employeeId, $gcr, '2025-10-27');

        $this->assertEquals('PLUMPANG', AttendanceLocationResolver::resolveBranchName($employeeId, '2024-07-21'));
        $this->assertEquals('CEMPAKA PUTIH', AttendanceLocationResolver::resolveBranchName($employeeId, '2024-07-22'));
        $this->assertEquals('CEMPAKA PUTIH', AttendanceLocationResolver::resolveBranchName($employeeId, '2025-10-26'));
        $this->assertEquals('GCR', AttendanceLocationResolver::resolveBranchName($employeeId, '2025-10-27'));
        $this->assertEquals('GCR', AttendanceLocationResolver::resolveBranchName($employeeId, '2026-08-13'));
    }

    public function test_attendance_exactly_on_transfer_date_uses_new_branch()
    {
        $alhijra = $this->createBranch('ALHIJRA');
        $gcr = $this->createBranch('GCR');

        $employeeId = $this->createEmployee($gcr);
        $this->createInitialPlacement($employeeId, $alhijra, '2024-01-01');
        $this->createTransfer($employeeId, $gcr, '2025-05-16');

        $this->assertEquals('GCR', AttendanceLocationResolver::resolveBranchName($employeeId, '2025-05-16'));
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-05-16'));
    }

    public function test_attendance_before_first_transfer_with_missing_initial_history_falls_back_to_current_branch()
    {
        $gcr = $this->createBranch('GCR');

        // No employee_branch_histories reference recorded yet; before the
        // first transfer the resolver falls back to the current branch.
        $employeeId = $this->createEmployee($gcr);

        $this->createTransfer($employeeId, $gcr, '2025-05-16');

        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-05-15'));
        $this->assertEquals('GCR', AttendanceLocationResolver::resolveBranchName($employeeId, '2025-05-15'));
    }

    public function test_initial_history_row_is_immutable_across_transfers()
    {
        $plumpang = $this->createBranch('PLUMPANG');
        $ho = $this->createBranch('HO');
        $gcr = $this->createBranch('GCR');

        $employeeId = $this->createEmployee($gcr);
        $this->createInitialPlacement($employeeId, $plumpang, '2024-01-17');
        $this->createTransfer($employeeId, $ho, '2024-07-22');
        $this->createTransfer($employeeId, $gcr, '2025-10-27');

        // Transferring must not rewrite the immutable initial branch reference.
        $initial = DB::table('employee_branch_histories')
            ->where('employee_id', $employeeId)
            ->orderBy('effective_date', 'asc')
            ->first();

        $this->assertEquals($plumpang, $initial->branch_id);

        // And the timeline still resolves correctly around the first transfer.
        $this->assertEquals($plumpang, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-21'));
        $this->assertEquals($ho, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-22'));
    }

    public function test_monthly_attendance_resolves_branch_per_date_across_mid_month_transfer()
    {
        $gcr = $this->createBranch('GCR');
        $cempakaPutih = $this->createBranch('CEMPAKA PUTIH');

        // Merly: initial GCR, transfer to Cempaka Putih on 2024-07-22,
        // transfer back to GCR on 2025-10-27.
        $employeeId = $this->createEmployee($gcr);
        $this->createInitialPlacement($employeeId, $gcr, '2024-01-17');
        $this->createTransfer($employeeId, $cempakaPutih, '2024-07-22');
        $this->createTransfer($employeeId, $gcr, '2025-10-27');

        // July 2024: days 1-21 GCR, days 22-31 Cempaka Putih.
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-01'));
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-21'));
        $this->assertEquals($cempakaPutih, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-22'));
        $this->assertEquals($cempakaPutih, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-31'));

        // August 2024: entire month Cempaka Putih.
        $this->assertEquals($cempakaPutih, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-08-01'));
        $this->assertEquals($cempakaPutih, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-08-31'));

        // October 2025: days 1-26 Cempaka Putih, days 27-31 GCR.
        $this->assertEquals($cempakaPutih, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-10-01'));
        $this->assertEquals($cempakaPutih, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-10-26'));
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-10-27'));
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-10-31'));

        // November 2025 onwards: entire month GCR.
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-11-01'));
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-11-30'));
    }

    public function test_ensure_initial_placement_records_old_branch_before_transfer()
    {
        $gcr = $this->createBranch('GCR');
        $plumpang = $this->createBranch('PLUMPANG');

        // Employee currently placed at GCR but created without any history row
        // (e.g. hired before this feature, or via a flow that skips recording).
        $employeeId = $this->createEmployee($gcr);
        $this->createAttendance($employeeId, '2024-01-10');

        // Simulate TransferController::store: ensure the initial placement is
        // recorded (GCR, from earliest attendance) before the transfer row.
        EmployeeBranchHistory::ensureInitialPlacement($employeeId);
        $this->createTransfer($employeeId, $plumpang, '2024-07-22');

        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-21'));
        $this->assertEquals('GCR', AttendanceLocationResolver::resolveBranchName($employeeId, '2024-07-21'));
        $this->assertEquals($plumpang, AttendanceLocationResolver::resolveBranchId($employeeId, '2024-07-22'));
    }

    public function test_ensure_initial_placement_falls_back_to_company_doj()
    {
        $gcr = $this->createBranch('GCR');
        $ho = $this->createBranch('HO');

        $employeeId = $this->createEmployeeWithDoj($gcr, '2024-01-17');

        EmployeeBranchHistory::ensureInitialPlacement($employeeId);
        $this->createTransfer($employeeId, $ho, '2025-05-16');

        $initial = DB::table('employee_branch_histories')
            ->where('employee_id', $employeeId)
            ->orderBy('effective_date', 'asc')
            ->first();

        $this->assertEquals($gcr, (int) $initial->branch_id);
        $this->assertEquals('2024-01-17', $initial->effective_date);
        $this->assertEquals($gcr, AttendanceLocationResolver::resolveBranchId($employeeId, '2025-05-15'));
    }

    public function test_ensure_initial_placement_is_idempotent()
    {
        $gcr = $this->createBranch('GCR');

        $employeeId = $this->createEmployee($gcr);

        EmployeeBranchHistory::ensureInitialPlacement($employeeId);
        EmployeeBranchHistory::ensureInitialPlacement($employeeId);

        $this->assertEquals(1, DB::table('employee_branch_histories')->where('employee_id', $employeeId)->count());
    }

    public function test_ensure_initial_placement_skips_employee_with_existing_history()
    {
        $gcr = $this->createBranch('GCR');

        $employeeId = $this->createEmployee($gcr);
        $this->createInitialPlacement($employeeId, $gcr, '2024-01-17');

        EmployeeBranchHistory::ensureInitialPlacement($employeeId);

        $this->assertEquals(1, DB::table('employee_branch_histories')->where('employee_id', $employeeId)->count());
    }
}