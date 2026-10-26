<?php

namespace App\Services;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Aturan absensi karyawan shift (Model Y / per-shift).
 *
 * Setiap shift punya jendela check-in & check-out sendiri. Sebuah "sesi"
 * adalah rangkaian shift yang bersambung (kontigu) dalam sehari:
 *  - shift pertama sesi mendapat kelonggaran check-in 30 menit lebih awal;
 *  - shift terakhir sesi mendapat kelonggaran check-out 2 jam;
 *  - shift lain: check-in mulai saat shift mulai, check-out mulai saat shift selesai.
 *
 * Urutan wajib: shift berikutnya tidak bisa di-check-in selama shift
 * sebelumnya sudah check-in tetapi belum check-out.
 */
class ShiftSequenceService
{
    public const CHECK_IN_LEAD_MINUTES = 30;

    public const CHECK_OUT_GRACE_MINUTES = 120;

    /**
     * Seluruh rangkaian yang relevan di sekitar $now.
     *
     * @return Collection<int, array>
     */
    public function sequencesForEmployee(Employee $employee, Carbon $now): Collection
    {
        $from = $now->copy()->subDays(3)->startOfDay();
        $to = $now->copy()->addDays(2)->endOfDay();

        $schedules = $employee->shiftSchedules()
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<=', $to->toDateString())
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return $this->buildSequences($employee->id, $schedules);
    }

    /**
     * Bangun rangkaian dari kumpulan baris roster.
     *
     * @param  iterable<EmployeeShiftSchedule>  $schedules
     * @return Collection<int, array>
     */
    public function buildSequences(int $employeeId, iterable $schedules): Collection
    {
        $entries = collect();
        foreach ($schedules as $schedule) {
            $entry = $this->buildEntry($employeeId, $schedule);
            if ($entry !== null) {
                $entries->push($entry);
            }
        }

        $entries = $entries->sortBy(fn ($e) => $e['start_at']->getTimestamp())->values();

        $sequences = collect();
        $current = [];

        foreach ($entries as $entry) {
            if (empty($current)) {
                $current = [$entry];
                continue;
            }

            $last = end($current);
            if ($entry['start_at']->getTimestamp() <= $last['end_at']->getTimestamp()) {
                $current[] = $entry;
            } else {
                $sequences->push($this->finalizeSequence($current));
                $current = [$entry];
            }
        }

        if (!empty($current)) {
            $sequences->push($this->finalizeSequence($current));
        }

        return $sequences;
    }

    /**
     * Semua shift (flatten dari rangkaian), terurut, lengkap dengan penanda posisi & jendela.
     *
     * @return Collection<int, array>
     */
    public function allShifts(Employee $employee, Carbon $now): Collection
    {
        return $this->sequencesForEmployee($employee, $now)
            ->flatMap(fn ($sequence) => $sequence['shifts'])
            ->values();
    }

    /**
     * Tentukan aksi absensi yang relevan saat ini (untuk dashboard).
     *
     * @return array{mode: string, shift: ?array, can_act: bool, message: ?string}
     */
    public function resolveAction(Employee $employee, Carbon $now): array
    {
        $shifts = $this->allShifts($employee, $now);

        // 1. Check-out diprioritaskan: shift yang sudah check-in tapi belum checkout
        //    — hanya jika jendela check-out masih terbuka (belum expired)
        $pending = $shifts
            ->filter(fn ($s) => $s['attendance']
                && $this->isCheckedIn($s)
                && ($s['attendance']->clock_out == '00:00:00' || $s['attendance']->clock_out == $s['attendance']->clock_in))
            ->filter(fn ($s) => $this->withinCheckOutDeadline($s, $now))
            ->sortBy(fn ($s) => $s['start_at']->getTimestamp())
            ->first();

        if ($pending) {
            $canAct = $now->greaterThanOrEqualTo($pending['check_out_open_at'])
                && $this->withinCheckOutDeadline($pending, $now);

            return [
                'mode' => 'check_out',
                'shift' => $pending,
                'can_act' => $canAct,
                'message' => $canAct ? null : $this->checkOutUnavailableMessage($pending, $now),
            ];
        }

        // 2. Check-in: shift tanpa attendance pada jendela yang terbuka.
        $checkInCandidate = $shifts
            ->filter(function ($s) use ($now) {
                if ($this->isCheckedIn($s)) {
                    return false;
                }

                return $now->greaterThanOrEqualTo($s['check_in_open_at'])
                    && $now->lessThanOrEqualTo($s['end_at']);
            })
            ->sortBy(fn ($s) => $s['start_at']->getTimestamp())
            ->first();

        if ($checkInCandidate) {
            $blocked = $this->hasPendingEarlierShift($shifts, $checkInCandidate, $now);

            return [
                'mode' => 'check_in',
                'shift' => $checkInCandidate,
                'can_act' => !$blocked,
                'message' => $blocked ? __('Check-out shift sebelumnya terlebih dahulu.') : null,
            ];
        }

        // 3. Tidak ada aksi: pilih shift berikutnya/terdekat untuk menampilkan info & pesan.
        $nearest = $this->nearestShift($shifts, $now);

        if ($nearest) {
            $message = $now->lessThan($nearest['check_in_open_at'])
                ? __('Check-in tersedia mulai :datetime.', ['datetime' => $nearest['check_in_open_at']->locale('id')->isoFormat('D MMMM Y HH:mm')])
                : __('Waktu check-in telah berakhir.');

            return ['mode' => 'none', 'shift' => $nearest, 'can_act' => false, 'message' => $message];
        }

        return ['mode' => 'none', 'shift' => null, 'can_act' => false, 'message' => null];
    }

    /**
     * Validasi check-in per shift.
     *
     * @return array{allowed: bool, message: ?string, shift: ?array}
     */
    public function validateCheckIn(Employee $employee, Carbon $now, int $shiftTypeId, ?string $shiftDate = null): array
    {
        $shifts = $this->allShifts($employee, $now);
        $shift = $this->findShift($shifts, $shiftTypeId, $shiftDate);

        if (!$shift) {
            return ['allowed' => false, 'message' => __('Shift yang dipilih tidak sesuai dengan penugasan Anda.'), 'shift' => null];
        }

        if ($this->hasPendingEarlierShift($shifts, $shift, $now)) {
            return ['allowed' => false, 'message' => __('Check-out shift sebelumnya terlebih dahulu.'), 'shift' => $shift];
        }

        if ($this->isCheckedIn($shift)) {
            return ['allowed' => false, 'message' => __('Anda sudah melakukan check-in untuk shift ini.'), 'shift' => $shift];
        }

        if ($now->lessThan($shift['check_in_open_at'])) {
            return ['allowed' => false, 'message' => __('Check-in tersedia mulai :datetime.', ['datetime' => $shift['check_in_open_at']->locale('id')->isoFormat('D MMMM Y HH:mm')]), 'shift' => $shift];
        }

        if ($now->greaterThan($shift['end_at'])) {
            return ['allowed' => false, 'message' => __('Waktu check-in telah berakhir.'), 'shift' => $shift];
        }

        return ['allowed' => true, 'message' => null, 'shift' => $shift];
    }

    /**
     * Validasi check-out per shift.
     *
     * @return array{allowed: bool, message: ?string, shift: ?array}
     */
    public function validateCheckOut(Employee $employee, Carbon $now, AttendanceEmployee $attendance): array
    {
        $shifts = $this->allShifts($employee, $now);
        $shift = $this->findShift($shifts, (int) $attendance->shift_type_id, (string) $attendance->date);

        if (!$shift || $attendance->employee_id != $employee->id) {
            return ['allowed' => false, 'message' => __('Data attendance tidak ditemukan atau bukan milik Anda.'), 'shift' => null];
        }

        if (!$attendance->clock_in || $attendance->clock_in == '00:00:00') {
            return ['allowed' => false, 'message' => __('Anda belum melakukan check-in.'), 'shift' => $shift];
        }

        if ($attendance->clock_out != '00:00:00' && $attendance->clock_out != $attendance->clock_in) {
            return ['allowed' => false, 'message' => __('Anda sudah melakukan check-out untuk shift ini.'), 'shift' => $shift];
        }

        if ($now->lessThan($shift['check_out_open_at'])) {
            return ['allowed' => false, 'message' => __('Check-out tersedia mulai :datetime.', ['datetime' => $shift['check_out_open_at']->locale('id')->isoFormat('D MMMM Y HH:mm')]), 'shift' => $shift];
        }

        if (!$this->withinCheckOutDeadline($shift, $now)) {
            return ['allowed' => false, 'message' => __('Waktu check-out telah berakhir.'), 'shift' => $shift];
        }

        return ['allowed' => true, 'message' => null, 'shift' => $shift];
    }

    /**
     * True bila jendela check-out shift ini masih terbuka (shift terakhir dibatasi 2 jam).
     */
    protected function withinCheckOutDeadline(array $shift, Carbon $now): bool
    {
        if ($shift['check_out_deadline_at'] === null) {
            return true;
        }

        return $now->lessThan($shift['check_out_deadline_at']);
    }

    /**
     * True bila shift sudah memiliki clock-in nyata (bukan placeholder izin).
     */
    protected function isCheckedIn(array $shift): bool
    {
        $attendance = $shift['attendance'];

        return !empty($attendance)
            && $attendance->clock_in != '00:00:00'
            && $attendance->clock_in !== null;
    }

    /**
     * True bila ada shift sebelumnya (dalam sesi yang sama) yang check-in tapi belum check-out
     * DAN jendela check-out shift tersebut masih terbuka (belum expired).
     */
    protected function hasPendingEarlierShift(Collection $shifts, array $target, Carbon $now): bool
    {
        return $shifts
            ->filter(function ($s) use ($target, $now) {
                if ($s['start_at']->getTimestamp() >= $target['start_at']->getTimestamp()) {
                    return false;
                }

                if (!$this->isCheckedIn($s)) {
                    return false;
                }

                if ($s['attendance']->clock_out != '00:00:00' && $s['attendance']->clock_out != $s['attendance']->clock_in) {
                    return false;
                }

                // Hanya blokir jika jendela check-out shift sebelumnya masih terbuka
                return $this->withinCheckOutDeadline($s, $now);
            })
            ->isNotEmpty();
    }

    protected function findShift(Collection $shifts, int $shiftTypeId, ?string $shiftDate): ?array
    {
        return $shifts->first(function ($s) use ($shiftTypeId, $shiftDate) {
            if ((int) $s['shift_type_id'] !== $shiftTypeId) {
                return false;
            }
            if ($shiftDate !== null && $s['shift_date'] !== $shiftDate) {
                return false;
            }

            return true;
        });
    }

    protected function nearestShift(Collection $shifts, Carbon $now): ?array
    {
        return $shifts
            ->filter(fn ($s) => $now->lessThanOrEqualTo($s['end_at']))
            ->sortBy(fn ($s) => $s['start_at']->getTimestamp())
            ->first()
            ?? $shifts->sortByDesc(fn ($s) => $s['end_at']->getTimestamp())->first();
    }

    public function checkInUnavailableMessage(Employee $employee, Carbon $now): string
    {
        $action = $this->resolveAction($employee, $now);

        if ($action['message']) {
            return $action['message'];
        }

        return $action['mode'] === 'check_out'
            ? __('Selesaikan check-out shift saat ini terlebih dahulu.')
            : __('Tidak ada shift yang dapat di-check-in saat ini.');
    }

    protected function checkOutUnavailableMessage(array $shift, Carbon $now): string
    {
        if ($now->lessThan($shift['check_out_open_at'])) {
            return __('Check-out tersedia mulai :datetime.', ['datetime' => $shift['check_out_open_at']->locale('id')->isoFormat('D MMMM Y HH:mm')]);
        }

        if ($shift['check_out_deadline_at'] !== null && $now->greaterThanOrEqualTo($shift['check_out_deadline_at'])) {
            return __('Waktu check-out telah berakhir.');
        }

        return __('Tidak ada shift yang dapat di-check-out saat ini.');
    }

    /**
     * Ubah satu baris roster menjadi entri shift dengan waktu absolut.
     */
    protected function buildEntry(int $employeeId, EmployeeShiftSchedule $schedule): ?array
    {
        $shiftDate = $schedule->date instanceof Carbon
            ? $schedule->date->toDateString()
            : (string) $schedule->date;

        $shiftTime = ShiftTime::where('shift_type_id', $schedule->shift_type_id)
            ->where('days', date('l', strtotime($shiftDate)))
            ->first();

        if (!$shiftTime || !$shiftTime->is_working || !$shiftTime->start_time || !$shiftTime->end_time) {
            return null;
        }

        $endDate = $schedule->end_date
            ? ($schedule->end_date instanceof Carbon ? $schedule->end_date->toDateString() : (string) $schedule->end_date)
            : $shiftDate;

        $startAt = Carbon::parse($shiftDate . ' ' . $shiftTime->start_time);
        $endAt = Carbon::parse($endDate . ' ' . $shiftTime->end_time);

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt->addDay();
        }

        $attendance = AttendanceEmployee::where('employee_id', $employeeId)
            ->where('date', $shiftDate)
            ->where('shift_type_id', $schedule->shift_type_id)
            ->first();

        return [
            'schedule_id' => $schedule->id,
            'shift_type_id' => (int) $schedule->shift_type_id,
            'shift_type_name' => $schedule->shiftType?->name ?? '-',
            'shift_date' => $shiftDate,
            'end_date' => $endAt->toDateString(),
            'start_time' => $shiftTime->start_time,
            'end_time' => $shiftTime->end_time,
            'is_working' => (bool) $shiftTime->is_working,
            'is_cross_day' => $endAt->toDateString() !== $startAt->toDateString(),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'shift_time' => $shiftTime,
            'attendance' => $attendance,
        ];
    }

    /**
     * Lengkapi metadata rangkaian dan jendela per-shift.
     */
    protected function finalizeSequence(array $shifts): array
    {
        usort($shifts, fn ($a, $b) => $a['start_at']->getTimestamp() <=> $b['start_at']->getTimestamp());

        $count = count($shifts);

        foreach ($shifts as $index => &$shift) {
            $isFirst = $index === 0;
            $isLast = $index === $count - 1;

            $shift['sequence_index'] = null;
            $shift['order_in_sequence'] = $index;
            $shift['is_first_in_sequence'] = $isFirst;
            $shift['is_last_in_sequence'] = $isLast;

            // Shift pertama sesi mendapat kelonggaran check-in 30 menit.
            $shift['check_in_open_at'] = $isFirst
                ? $shift['start_at']->copy()->subMinutes(self::CHECK_IN_LEAD_MINUTES)
                : $shift['start_at']->copy();
            $shift['check_in_close_at'] = $shift['end_at']->copy();

            // Check-out mulai saat shift selesai; hanya shift terakhir yang dibatasi 2 jam.
            $shift['check_out_open_at'] = $shift['end_at']->copy();
            $shift['check_out_deadline_at'] = $isLast
                ? $shift['end_at']->copy()->addMinutes(self::CHECK_OUT_GRACE_MINUTES)
                : null;
        }
        unset($shift);

        $first = $shifts[0];
        $last = $shifts[$count - 1];

        return [
            'shifts' => $shifts,
            'first' => $first,
            'last' => $last,
            'first_shift_type_id' => $first['shift_type_id'],
            'last_shift_type_id' => $last['shift_type_id'],
            'first_shift_date' => $first['shift_date'],
            'first_start_at' => $first['start_at']->copy(),
            'first_end_at' => $first['end_at']->copy(),
            'check_in_open_at' => $first['check_in_open_at']->copy(),
            'last_end_at' => $last['end_at']->copy(),
            'check_out_open_at' => $last['check_out_open_at']->copy(),
            'check_out_deadline_at' => $last['check_out_deadline_at']->copy(),
        ];
    }
}
