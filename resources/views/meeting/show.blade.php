<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Title') }}</th>
                    <td>{{ !empty($meetings->title) ? $meetings->title : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Meeting Type') }}</th>
                    <td>{{ !empty($meetings->meeting_type) ? $meetings->meeting_type : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Meeting URL') }}</th>
                    <td>{{ !empty($meetings->url) ? $meetings->url : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Meeting Password') }}</th>
                    <td>{{ !empty($meetings->password) ? $meetings->password : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Time') }}</th>
                    <td>{{ !empty($meetings->start_time) ? $meetings->start_time : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Time') }}</th>
                    <td>{{ !empty($meetings->end_time) ? $meetings->end_time : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <td>{{ !empty($branch) ? $branch->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Department') }}</th>
                    <td>{{ implode(' ; ', $departments) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Employees') }}</th>
                    <td>{{ implode(' ; ', $employees) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Note') }}</th>
                    <td>{{ !empty($meetings->note) ? $meetings->note : '' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>