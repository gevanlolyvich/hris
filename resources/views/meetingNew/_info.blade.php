@php
    $currentEmployee = \App\Models\Employee::where('user_id', Auth::id())->first();
    $attendeeEmployeeIds = $meeting->attendees->pluck('employee_id')->toArray();
    $isAttendee = $currentEmployee && in_array($currentEmployee->id, $attendeeEmployeeIds);
    $myResult = $currentEmployee ? $meeting->results->where('employee_id', $currentEmployee->id)->first() : null;
@endphp

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table">
                <tr>
                    <th>{{ __('Title') }}</th>
                    <td>{{ $meeting->title }}</td>
                </tr>
                <tr>
                    <th>{{ __('Meeting Type') }}</th>
                    <td>{{ $meeting->meeting_type }}</td>
                </tr>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <td>{{ $meeting->meeting_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Time') }}</th>
                    <td>{{ \Carbon\Carbon::parse($meeting->meeting_time)->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Deadline') }}</th>
                    <td>{{ $meeting->deadline->format('d M Y H:i') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($meeting->isLocked())
                            <span class="badge bg-danger">{{ __('Di Tutup') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('Open') }}</span>
                        @endif
                    </td>
                </tr>
                @if(!empty($documentUrls))
                <tr>
                    <th>{{ __('Document') }}</th>
                    <td>
                        @foreach($documentUrls as $docUrl)
                            <a href="{{ $docUrl }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">
                                <i class="ti ti-download"></i> {{ __('Download') }}
                            </a>
                        @endforeach
                    </td>
                </tr>
                @endif
                <tr>
                    <th>{{ __('Attendees') }}</th>
                    <td>
                        @foreach($meeting->attendees as $attendee)
                            <span class="badge bg-info">{{ $attendee->employee->name }} ({{ $attendee->employee->department ? $attendee->employee->department->name : '-' }})</span>
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>