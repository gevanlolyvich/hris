@extends('layouts.admin')

@section('page-title')
    {{ __('Jadwal Shift') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('attendanceemployee.index') }}">{{ __('Timesheet') }}</a></li>
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

        $palette = ['primary', 'info', 'warning', 'success', 'danger', 'secondary', 'dark'];
        $shiftMeta = [];
        $colorIndex = 0;
        foreach ($shifts as $shiftId => $shift) {
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
            $shiftMeta[$shiftId] = [
                'name' => $shift->name,
                'color' => $palette[$colorIndex % count($palette)],
                'times' => $times,
                'crossDay' => $crossDay,
            ];
            $colorIndex++;
        }
    @endphp

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h5>{{ __('Jadwal Shift Saya') }} | {{ $employee->name }}</h5>
                <span class="badge bg-secondary rounded-pill">{{ __('Read Only') }}</span>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'my-shift-schedule.index', 'method' => 'get', 'class' => 'mb-3']) }}
                <div class="row align-items-end">
                    <div class="col-md-4">
                        {{ Form::label('month', __('Bulan'), ['class' => 'col-form-label']) }}
                        <input type="month" name="month" id="month" value="{{ $monthYear }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">{{ __('Tampilkan') }}</button>
                    </div>
                </div>
                {{ Form::close() }}

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
                                        <td class="cal-cell @if (!$cell['inMonth']) text-muted bg-light @endif">
                                            <div class="cal-date">{{ $cell['day'] }}</div>
                                            <div class="cal-chips">
                                                @foreach ($schedule[$cell['date']] ?? [] as $row)
                                                    @php
                                                        $meta = $shiftMeta[(int) $row->shift_type_id] ?? null;
                                                        $dayName = date('l', strtotime($cell['date']));
                                                    @endphp
                                                    @if ($meta)
                                                        <span class="badge bg-{{ $meta['color'] }} mb-1">
                                                            {{ $meta['name'] }}
                                                            @if (($meta['times'][$dayName]['working'] ?? false) && $meta['times'][$dayName]['start'] && $meta['times'][$dayName]['end'])
                                                                ({{ $meta['times'][$dayName]['start'] }}-{{ $meta['times'][$dayName]['end'] }})
                                                            @endif
                                                        </span>
                                                        @if ($row->end_date && $row->end_date != $cell['date'])
                                                            <br><small class="text-muted">{{ __('s/d') }} {{ \Auth::user()->dateFormat($row->end_date) }}</small>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($shifts->isNotEmpty())
                    <h6 class="mt-3">{{ __('Keterangan Shift') }}</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($shiftMeta as $meta)
                            <div class="border rounded p-2">
                                <span class="badge bg-{{ $meta['color'] }}">{{ $meta['name'] }}</span>
                                @if ($meta['crossDay'])
                                    <small class="text-muted d-block">{{ __('Shift lintas hari') }}</small>
                                @endif
                                <ul class="list-unstyled mb-0 mt-1">
                                    @foreach ($meta['times'] as $day => $t)
                                        @if ($t['working'])
                                            <li><small>{{ __($day) }}: {{ $t['start'] }} - {{ $t['end'] }}</small></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info mt-3 mb-0">
                        {{ __('Belum ada jadwal shift untuk bulan ini.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
