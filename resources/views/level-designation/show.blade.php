<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Level Name') }}</th>
                    <td>{{ !empty($level->name) ? $level->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Goal Weight') }}</th>
                    <td>{{ !empty($level->goal_weight) ? $level->goal_weight : '0' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Competency Weight') }}</th>
                    <td>{{ !empty($level->competency_weight) ? $level->competency_weight : '0' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Designation') }}</th>
                    {{-- <td>{{ implode(' ; ', $designations) }}</td> --}}
                    <td>
                        @foreach ($designations as $designation)
                            <span class="btn btn-sm btn-primary mb-2">- {{$designation}}</span>
                            <br>
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>