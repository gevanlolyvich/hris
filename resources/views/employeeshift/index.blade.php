@extends('layouts.admin')

@section('page-title')
    {{ __('Jadwal Shift Karyawan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Jadwal Shift') }}</li>
@endsection

@section('content')
    @php
        $monthYear = $month ?: date('Y-m');
        $m = date('m', strtotime($monthYear));
        $y = date('Y', strtotime($monthYear));
        $numDays = cal_days_in_month(CAL_GREGORIAN, $m, $y);

        $gridDays = [];
        $first = strtotime("{$y}-{$m}-01");
        $leading = ((int) date('N', $first)) - 1;
        $start = strtotime("-{$leading} days", $first);
        $totalCells = $leading + $numDays;
        $trailing = (7 - ($totalCells % 7)) % 7;
        $totalCells += $trailing;

        for ($i = 0; $i < $totalCells; $i++) {
            $ts = strtotime("+{$i} days", $start);
            $gridDays[] = [
                'date' => date('Y-m-d', $ts),
                'day' => date('j', $ts),
                'inMonth' => date('n', $ts) == $m,
            ];
        }

        $shiftOptions = [];
        $palette = ['primary', 'info', 'warning', 'success', 'danger', 'secondary', 'dark'];
        foreach ($shifts as $idx => $shift) {
            $times = [];
            $crossDay = false;
            foreach ($shift->shiftTimes as $st) {
                $times[$st->days] = [
                    'start' => substr($st->start_time ?? '', 0, 5),
                    'end' => substr($st->end_time ?? '', 0, 5),
                    'working' => (bool) $st->is_working,
                ];
                if ($st->is_working && $st->start_time && $st->end_time && $st->start_time > $st->end_time) {
                    $crossDay = true;
                }
            }
            $shiftOptions[] = [
                'id' => $shift->id,
                'name' => $shift->name,
                'color' => $palette[$idx % count($palette)],
                'times' => $times,
                'crossDay' => $crossDay,
            ];
        }

        $initialSchedule = [];
        foreach ($schedule as $date => $rows) {
            $entries = [];
            foreach ($rows as $row) {
                $entries[(int) $row->shift_type_id] = $row->end_date ?? $date;
            }
            $initialSchedule[$date] = $entries;
        }
    @endphp

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Pilih Karyawan Shift') }}</h5>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'employee-shift-schedule.index', 'method' => 'get']) }}
                <div class="row align-items-end">
                    <div class="col-md-4">
                        {{ Form::label('employee_id', __('Karyawan'), ['class' => 'col-form-label']) }}
                        <select name="employee_id" id="employee_id" class="form-control select2">
                            <option value="">{{ __('Pilih Karyawan') }}</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" @selected($selectedEmployee && $selectedEmployee->id == $emp->id)>
                                    {{ $emp->name . ' | ' . ($emp->employeeType?->name ?? '-') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        {{ Form::label('month', __('Bulan'), ['class' => 'col-form-label']) }}
                        <input type="month" name="month" id="month" value="{{ $monthYear }}" min="{{ date('Y-m') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">{{ __('Tampilkan') }}</button>
                        @if ($selectedEmployee)
                            <button type="button" class="btn btn-success" id="save-schedule">{{ __('Simpan Jadwal') }}</button>
                        @endif
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>

        @if ($selectedEmployee)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5>{{ __('Kalender Shift') }} | {{ $selectedEmployee->name }}</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" id="shift-counter">0 / 20 shift</span>
                        <button type="button" class="btn btn-sm btn-danger" id="clear-month">{{ __('Hapus Semua') }}</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="table-responsive">
                                <table class="table table-bordered calendar-shift">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Senin') }}</th>
                                            <th>{{ __('Selasa') }}</th>
                                            <th>{{ __('Rabu') }}</th>
                                            <th>{{ __('Kamis') }}</th>
                                            <th>{{ __('Jumat') }}</th>
                                            <th>{{ __('Sabtu') }}</th>
                                            <th>{{ __('Minggu') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (array_chunk($gridDays, 7) as $week)
                                            <tr>
                                                @foreach ($week as $cell)
                                                    <td class="cal-cell @if (!$cell['inMonth']) text-muted bg-light @endif @if ($cell['date'] < date('Y-m-d')) cal-cell-locked @endif"
                                                        data-date="{{ $cell['date'] }}">
                                                        <div class="cal-date">{{ $cell['day'] }}</div>
                                                        <div class="cal-chips" id="chips-{{ $cell['date'] }}"></div>
                                                        <div class="cal-add text-center mt-1">
                                                            @if ($cell['date'] >= date('Y-m-d'))
                                                                <button type="button" class="btn btn-sm btn-outline-secondary cal-add-btn">
                                                                    <i class="ti ti-plus"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card border">
                                <div class="card-header">
                                    <h6>{{ __('Shift Tray') }}</h6>
                                    <small class="text-muted">{{ __('Drag shift ke tanggal') }}</small>
                                </div>
                                <div class="card-body" id="shift-tray">
                                    @foreach ($shiftOptions as $so)
                                        <div class="alert alert-{{ $so['color'] }} py-2 px-3 shift-tray-chip" draggable="true"
                                            data-shift-id="{{ $so['id'] }}">
                                            <strong>{{ $so['name'] }}</strong>
                                            @if (!empty($so['times']))
                                                <div class="small">
                                                    @foreach ($so['times'] as $day => $t)
                                                        @if ($t['working'])
                                                            <span>{{ $day }}: {{ $t['start'] }}-{{ $t['end'] }}</span><br>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-0">{{ __('Pilih karyawan shift dan bulan untuk mulai mengatur jadwal.') }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="addShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Pilih Shift') }} - <span id="modal-date-label"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-shift-list" class="d-grid gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="endDateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Tanggal Selesai Shift') }} - <span id="end-date-shift-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="end-date-mode" value="add">
                    <input type="hidden" id="end-date-target-date" value="">
                    <input type="hidden" id="end-date-target-shift" value="">
                    <div class="form-group">
                        {{ Form::label('end_date_input', __('Tanggal selesai'), ['class' => 'col-form-label']) }}
                        <input type="date" id="end_date_input" class="form-control" min="">
                        <small class="text-muted">{{ __('Tentukan kapan shift ini berakhir (mis. shift malam berakhir keesokan paginya).') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="end-date-cancel">{{ __('Batal') }}</button>
                    <button type="button" class="btn btn-danger" id="end-date-remove" style="display:none">{{ __('Hapus Shift') }}</button>
                    <button type="button" class="btn btn-primary" id="end-date-save">{{ __('Simpan') }}</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .calendar-shift td {
            min-width: 120px;
            height: 110px;
            vertical-align: top;
            cursor: pointer;
        }
        .calendar-shift td.cal-drop-hover {
            outline: 2px dashed #007bff;
            outline-offset: -2px;
        }
        .cal-chip {
            display: block;
            margin: 2px 0;
            width: fit-content;
        }
        .shift-tray-chip {
            cursor: grab;
            margin-bottom: 0.5rem;
        }
        .calendar-shift td.cal-cell-locked {
            background-color: #f8f9fa;
        }
        .calendar-shift td.cal-cell-locked .cal-date {
            color: #adb5bd;
        }
    </style>
@endsection

@push('scripts')
    <script>
        (function() {
            var SHIFT_OPTIONS = @json($shiftOptions);
            var INITIAL_SCHEDULE = @json($initialSchedule);
            var EMPLOYEE_ID = {{ $selectedEmployee?->id ?? 'null' }};
            var MONTH = '{{ $monthYear }}';
            var TODAY = @json(date('Y-m-d'));
            var TARGET = 20;

            function isLockedDate(date) {
                return date < TODAY;
            }

            var scheduleData = {};
            Object.keys(INITIAL_SCHEDULE).forEach(function(date) {
                scheduleData[date] = {};
                var entries = INITIAL_SCHEDULE[date];
                Object.keys(entries).forEach(function(id) {
                    scheduleData[date][id] = entries[id];
                });
            });

            var colors = {};
            var crossDays = {};
            SHIFT_OPTIONS.forEach(function(so) {
                colors[so.id] = so.color;
                crossDays[so.id] = !!so.crossDay;
            });

            function shiftName(id) {
                var found = SHIFT_OPTIONS.find(function(so) { return so.id == id; });
                return found ? found.name : ('Shift #' + id);
            }

            function shiftColor(id) {
                return colors[id] || 'secondary';
            }

            function shiftIsCrossDay(id) {
                return !!crossDays[id];
            }

            function entriesFor(date) {
                if (!scheduleData[date]) scheduleData[date] = {};
                return scheduleData[date];
            }

            function totalShifts() {
                var total = 0;
                Object.keys(scheduleData).forEach(function(date) {
                    total += Object.keys(scheduleData[date]).length;
                });
                return total;
            }

            function renderCounter() {
                var el = document.getElementById('shift-counter');
                if (!el) return;
                var total = totalShifts();
                el.textContent = total + ' / ' + TARGET + ' shift';
                el.className = 'badge rounded-pill ' + (total < TARGET ? 'bg-success' : (total == TARGET ? 'bg-primary' : 'bg-danger'));
            }

            function renderChips() {
                Object.keys(scheduleData).forEach(function(date) {
                    var container = document.getElementById('chips-' + date);
                    if (!container) return;
                    container.innerHTML = '';
                    var entries = scheduleData[date];
                    Object.keys(entries).forEach(function(id) {
                        var locked = isLockedDate(date);
                        var endDate = entries[id];
                        var isCross = shiftIsCrossDay(id);
                        var chip = document.createElement('span');
                        chip.className = 'badge bg-' + shiftColor(id) + ' cal-chip mb-1';
                        var label = shiftName(id);
                        if (endDate && endDate !== date) {
                            label += ' (' + endDate.slice(5) + ' selesai)';
                        }
                        chip.textContent = label + (locked ? '' : ' \u00d7');
                        if (locked) {
                            chip.style.cursor = 'not-allowed';
                            chip.title = 'Tanggal sudah terlewat';
                        } else {
                            chip.style.cursor = 'pointer';
                            chip.title = (isCross ? 'Klik untuk ubah tanggal selesai' : 'Klik untuk hapus');
                            chip.addEventListener('click', function(e) {
                                e.stopPropagation();
                                if (isCross) {
                                    openEndDateModal(date, id, 'edit');
                                } else {
                                    removeShift(date, id);
                                }
                            });
                        }
                        container.appendChild(chip);
                    });
                });
                renderCounter();
            }

            function addShift(date, id, endDate) {
                var entries = entriesFor(date);
                entries[id] = endDate || date;
                renderChips();
            }

            function removeShift(date, id) {
                if (!scheduleData[date]) return;
                delete scheduleData[date][id];
                renderChips();
            }

            // ---- End date (cross-day) modal ----
            function openEndDateModal(date, id, mode) {
                document.getElementById('end-date-mode').value = mode || 'add';
                document.getElementById('end-date-target-date').value = date;
                document.getElementById('end-date-target-shift').value = id;
                document.getElementById('end-date-shift-name').textContent = shiftName(id);
                var input = document.getElementById('end_date_input');
                input.min = date;
                var current = (scheduleData[date] && scheduleData[date][id]) ? scheduleData[date][id] : addDays(date, 1);
                input.value = current;
                document.getElementById('end-date-remove').style.display = (mode === 'edit') ? '' : 'none';
                $('#endDateModal').modal('show');
            }

            function addDays(dateStr, days) {
                var d = new Date(dateStr + 'T00:00:00');
                d.setDate(d.getDate() + days);
                var y = d.getFullYear();
                var m = ('0' + (d.getMonth() + 1)).slice(-2);
                var dd = ('0' + d.getDate()).slice(-2);
                return y + '-' + m + '-' + dd;
            }

            var endDateSaveBtn = document.getElementById('end-date-save');
            if (endDateSaveBtn) {
                endDateSaveBtn.addEventListener('click', function() {
                    var date = document.getElementById('end-date-target-date').value;
                    var id = document.getElementById('end-date-target-shift').value;
                    var val = document.getElementById('end_date_input').value;
                    if (val < date) {
                        show_toastr('Error', 'Tanggal selesai tidak boleh sebelum tanggal mulai.', 'error');
                        return;
                    }
                    addShift(date, id, val);
                    $('#endDateModal').modal('hide');
                });
            }

            var endDateRemoveBtn = document.getElementById('end-date-remove');
            if (endDateRemoveBtn) {
                endDateRemoveBtn.addEventListener('click', function() {
                    var date = document.getElementById('end-date-target-date').value;
                    var id = document.getElementById('end-date-target-shift').value;
                    removeShift(date, id);
                    $('#endDateModal').modal('hide');
                });
            }

            var endDateCancelBtn = document.getElementById('end-date-cancel');
            if (endDateCancelBtn) {
                endDateCancelBtn.addEventListener('click', function() {
                    $('#endDateModal').modal('hide');
                });
            }

            document.querySelectorAll('.shift-tray-chip').forEach(function(chip) {
                chip.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', chip.getAttribute('data-shift-id'));
                    chip.classList.add('opacity-50');
                });
                chip.addEventListener('dragend', function(e) {
                    chip.classList.remove('opacity-50');
                });
            });

            document.querySelectorAll('.cal-cell').forEach(function(cell) {
                cell.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    cell.classList.add('cal-drop-hover');
                });
                cell.addEventListener('dragleave', function(e) {
                    cell.classList.remove('cal-drop-hover');
                });
                cell.addEventListener('drop', function(e) {
                    e.preventDefault();
                    cell.classList.remove('cal-drop-hover');
                    var id = parseInt(e.dataTransfer.getData('text/plain'), 10);
                    var date = cell.getAttribute('data-date');
                    if (isLockedDate(date)) {
                        show_toastr('Error', 'Tanggal sudah terlewat. Jadwal tidak dapat diubah.', 'error');
                        return;
                    }
                    var monthPrefix = MONTH.slice(0, 7);
                    if (date.indexOf(monthPrefix) !== 0) {
                        if (!confirm('Tanggal ' + date + ' di luar bulan ' + MONTH + '. Lanjutkan?')) {
                            return;
                        }
                    }
                    if (scheduleData[date] && scheduleData[date][id] !== undefined) return;
                    if (totalShifts() >= TARGET) {
                        if (!confirm('Melebihi target 20 shift. Lanjutkan?')) return;
                    }
                    if (shiftIsCrossDay(id)) {
                        openEndDateModal(date, id, 'add');
                    } else {
                        addShift(date, id, date);
                    }
                });
            });

            document.querySelectorAll('.cal-add-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var cell = btn.closest('.cal-cell');
                    openAddModal(cell.getAttribute('data-date'));
                });
            });

            function openAddModal(date) {
                document.getElementById('modal-date-label').textContent = date;
                var list = document.getElementById('modal-shift-list');
                list.innerHTML = '';
                SHIFT_OPTIONS.forEach(function(so) {
                    if (scheduleData[date] && scheduleData[date][so.id] !== undefined) return;
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-' + so.color + ' text-start w-100';
                    btn.textContent = so.name + (shiftIsCrossDay(so.id) ? ' \u00bb' : '');
                    btn.addEventListener('click', function() {
                        $('#addShiftModal').modal('hide');
                        if (shiftIsCrossDay(so.id)) {
                            openEndDateModal(date, so.id, 'add');
                        } else {
                            addShift(date, so.id, date);
                        }
                    });
                    list.appendChild(btn);
                });
                $('#addShiftModal').modal('show');
            }

            var clearBtn = document.getElementById('clear-month');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (!confirm('Hapus semua jadwal shift mulai hari ini? (Tanggal yang sudah lewat tidak diubah)')) return;
                    var prefix = MONTH.slice(0, 7);
                    Object.keys(scheduleData).forEach(function(date) {
                        if (date.indexOf(prefix) === 0 && !isLockedDate(date)) {
                            scheduleData[date] = {};
                        }
                    });
                    renderChips();
                });
            }

            var saveBtn = document.getElementById('save-schedule');
            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    var total = totalShifts();
                    if (total > TARGET && !confirm('Jumlah shift ' + total + ' melebihi target 20. Simpan tetap?')) {
                        return;
                    }
                    var prefix = MONTH.slice(0, 7);
                    var payload = {};
                    Object.keys(scheduleData).forEach(function(date) {
                        if (date.indexOf(prefix) !== 0) return;
                        var entries = scheduleData[date];
                        if (Object.keys(entries).length === 0) return;
                        payload[date] = {};
                        Object.keys(entries).forEach(function(id) {
                            payload[date][id] = entries[id];
                        });
                    });

                    saveBtn.disabled = true;
                    $.ajax({
                        url: '{{ route('employee-shift-schedule.store') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            employee_id: EMPLOYEE_ID,
                            month: MONTH,
                            schedule: payload,
                        },
                        success: function(res) {
                            show_toastr('Success', res.message, 'success');
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan';
                            show_toastr('Error', msg, 'error');
                        },
                        complete: function() {
                            saveBtn.disabled = false;
                        }
                    });
                });
            }

            renderChips();
        })();
    </script>
@endpush