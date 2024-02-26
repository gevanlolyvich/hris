<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ $goal?->employee?->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th>{{ __('Main Goal') }}</th>
                    <td>{{ $goal?->parent?->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <td>{{ $goal->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ $goal->start_date }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ $goal->end_date }}</td>
                </tr>
                <tr>
                    <th>{{ __('Target') }}</th>
                    <td>
                        {{ Form::textarea('target', $goal->target, ['class' => 'form-control' ,'rows'=>'3', 'disabled' => 'disabled']) }}
                    </td>
                    {{-- <td>{{ $goal->target }}</td> --}}
                </tr>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <td>
                        {{ Form::textarea('description', $goal->description, ['class' => 'form-control' ,'rows'=>'3', 'disabled' => 'disabled']) }}
                    </td>
                    {{-- <td>{{ $goal->description }}</td> --}}
                </tr>
                <tr>
                    <th>{{ __('Progress') }}</th>
                    <td>{{ $goal->goal ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Progress Percentage') }}</th>
                    <td>
                        <div class="progress-wrapper">
                            <span class="progress-percentage"><small
                                    class="font-weight-bold"></small>{{ $goal->progress }}%</span>
                            <div class="progress progress-xs mt-2 w-100">
                                <div class="progress-bar bg-{{ Utility::getProgressColor($goal->progress) }}"
                                    role="progressbar" aria-valuenow="{{ $goal->progress }}"
                                    aria-valuemin="0" aria-valuemax="100"
                                    style="width: {{ $goal->progress }}%;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                {{-- <tr>
                    <th>{{ __() }}</th>
                    <td>{{ $goal }}</td>
                </tr>
                <tr>
                    <th>{{ __() }}</th>
                    <td>{{ $goal }}</td>
                </tr>
                <tr>
                    <th>{{ __() }}</th>
                    <td>{{ $goal }}</td>
                </tr> --}}
            </table>
        </div>
    </div>
</div>