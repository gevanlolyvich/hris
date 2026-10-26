<?php

namespace Tests\Feature;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\ShiftType;
use App\Services\ShiftSequenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShiftSequenceTimeWindowTest extends TestCase
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
            $table->string('personel_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('shift_type_id')->nullable();
            $table->boolean('is_shift')->default(false);
            $table->integer('branch_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->string('coordinate')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shift_types', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_shift')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shift_times', function ($table) {
            $table->increments('id');
            $table->integer('shift_type_id');
            $table->string('days');
            $table->boolean('is_working')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_shift_schedules', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->date('date');
            $table->integer('shift_type_id');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_employees', function ($table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->date('date');
            $table->integer('shift_type_id')->nullable();
            $table->string('status')->nullable();
            $table->string('clock_in')->nullable();
            $table->string('clock_out')->nullable();
            $table->integer('attendance_status_id')->nullable();
            $table->integer('is_valid')->nullable();
            $table->timestamps();
        });
    }

    protected function makeEmployee(array $attrs = []): Employee
    {
        $employee = new Employee();
        $employee->forceFill(array_merge([
            'name' => 'Test',
            'personel_id' => 'PE-' . uniqid(),
            'is_shift' => 1,
            'is_active' => 1,
        ], $attrs));
        $employee->exists = false;
        $employee->save();

        return $employee;
    }

    protected function makeShiftType(int $id, string $name): void
    {
        ShiftType::unguarded(function () use ($id, $name) {
            $shiftType = new ShiftType();
            $shiftType->id = $id;
            $shiftType->name = $name;
            $shiftType->is_shift = 1;
            $shiftType->exists = false;
            $shiftType->save();
        });
    }

    protected function addShiftTime(int $shiftTypeId, string $start, string $end, string $day = 'Tuesday', bool $working = true): void
    {
        DB::table('shift_times')->insert([
            'shift_type_id' => $shiftTypeId,
            'days' => $day,
            'is_working' => $working ? 1 : 0,
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }

    protected function addSchedule(int $employeeId, int $shiftTypeId, string $date, ?string $endDate = null): void
    {
        DB::table('employee_shift_schedules')->insert([
            'employee_id' => $employeeId,
            'date' => $date,
            'shift_type_id' => $shiftTypeId,
            'end_date' => $endDate,
        ]);
    }

    protected function addAttendance(int $employeeId, int $shiftTypeId, string $date, string $clockIn = '08:00:00', string $clockOut = '00:00:00'): AttendanceEmployee
    {
        $row = new AttendanceEmployee();
        $row->employee_id = $employeeId;
        $row->date = $date;
        $row->shift_type_id = $shiftTypeId;
        $row->status = 'Present';
        $row->clock_in = $clockIn;
        $row->clock_out = $clockOut;
        $row->save();

        return $row;
    }

    protected function makeService(): ShiftSequenceService
    {
        return new ShiftSequenceService();
    }

    protected function findShift(ShiftSequenceService $service, Employee $employee, int $shiftTypeId, Carbon $now): ?array
    {
        return $service->allShifts($employee, $now)
            ->first(fn ($s) => (int) $s['shift_type_id'] === $shiftTypeId);
    }

    // Shift 1: 08:00-15:00, Shift 2: 15:00-23:00, Shift 3: 23:00-07:00 (lintas hari).

    public function test_1_shift_pertama_dapat_kelonggaran_check_in_30_menit(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');

        $service = $this->makeService();

        $shift = $this->findShift($service, $employee, 1, Carbon::create(2026, 10, 13, 8, 0, 0));
        $this->assertSame('07:30', $shift['check_in_open_at']->format('H:i'));
        $this->assertTrue($shift['is_first_in_sequence']);
        $this->assertTrue($shift['is_last_in_sequence']);

        // 07:29 = 31 menit sebelum shift → di luar jendela.
        $denied = $service->validateCheckIn($employee, Carbon::create(2026, 10, 13, 7, 29, 0), 1);
        $this->assertFalse($denied['allowed']);

        // 07:31 = 29 menit sebelum shift → sudah di dalam jendela.
        $allowed = $service->validateCheckIn($employee, Carbon::create(2026, 10, 13, 7, 31, 0), 1);
        $this->assertTrue($allowed['allowed']);
    }

    public function test_2_shift_kedua_tidak_dapat_kelonggaran_check_in(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addSchedule($employee->id, 2, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addShiftTime(2, '15:00:00', '23:00:00');

        $service = $this->makeService();
        $shift2 = $this->findShift($service, $employee, 2, Carbon::create(2026, 10, 13, 15, 0, 0));

        $this->assertFalse($shift2['is_first_in_sequence']);
        $this->assertTrue($shift2['is_last_in_sequence']);
        $this->assertSame('15:00', $shift2['check_in_open_at']->format('H:i'), 'Shift non-pertama dibuka tepat saat mulai.');
    }

    public function test_3_shift_kedua_terblokir_selama_shift_pertama_belum_check_out(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addSchedule($employee->id, 2, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addShiftTime(2, '15:00:00', '23:00:00');
        $this->addAttendance($employee->id, 1, '2026-10-13', '08:00:00', '00:00:00');

        $service = $this->makeService();
        $now = Carbon::create(2026, 10, 13, 15, 5, 0);

        $blocked = $service->validateCheckIn($employee, $now, 2);
        $this->assertFalse($blocked['allowed']);
        $this->assertSame('Check-out shift sebelumnya terlebih dahulu.', $blocked['message']);

        // Dashboard memprioritaskan check-out shift pertama.
        $action = $service->resolveAction($employee, $now);
        $this->assertSame('check_out', $action['mode']);
        $this->assertSame(1, $action['shift']['shift_type_id']);
        $this->assertTrue($action['can_act']);
    }

    public function test_4_shift_kedua_bisa_check_in_setelah_shift_pertama_check_out(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addSchedule($employee->id, 2, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addShiftTime(2, '15:00:00', '23:00:00');
        $this->addAttendance($employee->id, 1, '2026-10-13', '08:00:00', '15:05:00');

        $service = $this->makeService();
        $now = Carbon::create(2026, 10, 13, 15, 6, 0);

        $allowed = $service->validateCheckIn($employee, $now, 2);
        $this->assertTrue($allowed['allowed']);
    }

    public function test_5_check_out_shift_terakhir_dibatasi_2_jam(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 2, '2026-10-13');
        $this->addShiftTime(2, '15:00:00', '23:00:00');
        $attendance = $this->addAttendance($employee->id, 2, '2026-10-13', '15:00:00');

        $service = $this->makeService();

        $this->assertTrue(
            $service->validateCheckOut($employee, Carbon::create(2026, 10, 13, 23, 59, 0), $attendance)['allowed']
        );
        $this->assertTrue(
            $service->validateCheckOut($employee, Carbon::create(2026, 10, 14, 0, 59, 0), $attendance)['allowed']
        );

        $deadline = $service->validateCheckOut($employee, Carbon::create(2026, 10, 14, 1, 0, 0), $attendance);
        $this->assertFalse($deadline['allowed'], 'Batas 2 jam eksklusif (tepat 01:00 ditolak).');
    }

    public function test_6_check_out_ditolak_sebelum_shift_selesai(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $attendance = $this->addAttendance($employee->id, 1, '2026-10-13', '08:00:00');

        $service = $this->makeService();

        $this->assertFalse(
            $service->validateCheckOut($employee, Carbon::create(2026, 10, 13, 14, 59, 0), $attendance)['allowed']
        );
        $this->assertTrue(
            $service->validateCheckOut($employee, Carbon::create(2026, 10, 13, 15, 0, 0), $attendance)['allowed']
        );
    }

    public function test_7_check_out_shift_awal_tanpa_batas_2_jam(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addSchedule($employee->id, 2, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addShiftTime(2, '15:00:00', '23:00:00');
        $attendance = $this->addAttendance($employee->id, 1, '2026-10-13', '08:00:00');

        $service = $this->makeService();
        $shift1 = $this->findShift($service, $employee, 1, Carbon::create(2026, 10, 13, 16, 0, 0));

        $this->assertNull($shift1['check_out_deadline_at'], 'Shift non-terakhir tanpa batas 2 jam.');

        // 14:00 keesokan harinya pun check-out shift pertama masih dianggap valid oleh service.
        $this->assertTrue(
            $service->validateCheckOut($employee, Carbon::create(2026, 10, 14, 14, 0, 0), $attendance)['allowed']
        );
    }

    public function test_8_shift_lintas_hari_pertama_dan_terakhir(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 3, '2026-10-13', '2026-10-14');
        $this->addShiftTime(3, '23:00:00', '07:00:00');

        $service = $this->makeService();
        $shift = $this->findShift($service, $employee, 3, Carbon::create(2026, 10, 13, 23, 0, 0));

        $this->assertSame('22:30', $shift['check_in_open_at']->format('H:i'));
        $this->assertSame('2026-10-14 07:00', $shift['check_out_open_at']->format('Y-m-d H:i'));
        $this->assertSame('2026-10-14 09:00', $shift['check_out_deadline_at']->format('Y-m-d H:i'));
    }

    public function test_9_jeda_memisahkan_sesi_sehingga_shift_berikut_dapat_30_menit(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addSchedule($employee->id, 3, '2026-10-13', '2026-10-14');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addShiftTime(3, '23:00:00', '07:00:00');

        $service = $this->makeService();
        $sequences = $service->sequencesForEmployee($employee, Carbon::create(2026, 10, 13, 23, 0, 0));

        $this->assertCount(2, $sequences, 'Jeda 15:00-23:00 memisahkan menjadi dua sesi.');

        $shift3 = $this->findShift($service, $employee, 3, Carbon::create(2026, 10, 13, 23, 0, 0));
        $this->assertTrue($shift3['is_first_in_sequence']);
        $this->assertSame('22:30', $shift3['check_in_open_at']->format('H:i'), 'Shift 3 adalah shift pertama sesinya.');
    }

    public function test_10_checkout_belum_dibuka_sebelum_shift_selesai(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $attendance = $this->addAttendance($employee->id, 1, '2026-10-13', '08:00:00');

        $service = $this->makeService();
        $action = $service->resolveAction($employee, Carbon::create(2026, 10, 13, 14, 0, 0));

        $this->assertSame('check_out', $action['mode']);
        $this->assertFalse($action['can_act'], 'Tombol CLOCK OUT dinonaktifkan sampai jam shift selesai.');
        $this->assertStringContainsString('15:00', $action['message']);
    }

    public function test_11_tanpa_check_in_tidak_boleh_check_out(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $attendance = $this->addAttendance($employee->id, 1, '2026-10-13', '00:00:00');

        $service = $this->makeService();
        $validation = $service->validateCheckOut($employee, Carbon::create(2026, 10, 13, 15, 30, 0), $attendance);

        $this->assertFalse($validation['allowed']);
        $this->assertSame('Anda belum melakukan check-in.', $validation['message']);
    }

    public function test_12_check_in_kedua_ditolak_setelah_clock_in(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addAttendance($employee->id, 1, '2026-10-13', '08:01:00', '08:01:00');

        $service = $this->makeService();
        $validation = $service->validateCheckIn($employee, Carbon::create(2026, 10, 13, 8, 5, 0), 1);

        $this->assertFalse($validation['allowed']);
        $this->assertSame('Anda sudah melakukan check-in untuk shift ini.', $validation['message']);
    }

    public function test_13_absensi_shift_bukan_penugasan_karyawan_ditolak(): void
    {
        $shiftOwner = $this->makeEmployee();
        $other = $this->makeEmployee();

        $this->addSchedule($shiftOwner->id, 1, '2026-10-13');
        $this->addSchedule($other->id, 2, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');
        $this->addShiftTime(2, '15:00:00', '23:00:00');

        $service = $this->makeService();

        // Karyawan lain mencoba check-in pakai shift milik orang lain.
        $validation = $service->validateCheckIn($other, Carbon::create(2026, 10, 13, 15, 30, 0), 1);
        $this->assertFalse($validation['allowed']);
        $this->assertSame('Shift yang dipilih tidak sesuai dengan penugasan Anda.', $validation['message']);

        // Karyawan lain mencoba check-out baris attendance milik orang lain.
        $attendance = $this->addAttendance($shiftOwner->id, 1, '2026-10-13', '08:00:00');
        $validation = $service->validateCheckOut($other, Carbon::create(2026, 10, 13, 15, 30, 0), $attendance);
        $this->assertFalse($validation['allowed']);
        $this->assertSame('Data attendance tidak ditemukan atau bukan milik Anda.', $validation['message']);
    }

    public function test_14_aturan_menggunakan_jam_server_bukan_jam_klien(): void
    {
        $employee = $this->makeEmployee();
        $this->addSchedule($employee->id, 1, '2026-10-13');
        $this->addShiftTime(1, '08:00:00', '15:00:00');

        $service = $this->makeService();

        // Server masih 07:29:59 → ditolak, meskipun klien berpura-pura sudah 07:30.
        $serverNow = Carbon::create(2026, 10, 13, 7, 29, 59);
        $this->assertFalse($service->validateCheckIn($employee, $serverNow, 1)['allowed']);

        $validation = $service->validateCheckIn($employee, $serverNow, 1);
        $this->assertStringContainsString('Check-in tersedia mulai 13 Oktober 2026 07:30', $validation['message']);
    }
}
