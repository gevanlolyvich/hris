@php
    $selectedEmployeeIds = $meeting->attendees->pluck('employee_id')->toArray();
@endphp

{{ Form::model($meeting, ['route' => ['meeting-new.update', $meeting->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('title', __('Meeting Title'), ['class' => 'form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Meeting Title')]) }}
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('meeting_type', __('Meeting Type'), ['class' => 'form-label']) }}
                {{ Form::select('meeting_type', ['Offline' => 'Offline', 'Online' => 'Online', 'Hybrid' => 'Hybrid'], null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Meeting Type')]) }}
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('meeting_date', __('Meeting Date'), ['class' => 'form-label']) }}
                {{ Form::date('meeting_date', $meeting->meeting_date->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('meeting_time', __('Meeting Time'), ['class' => 'form-label']) }}
                {{ Form::time('meeting_time', \Carbon\Carbon::parse($meeting->meeting_time)->format('H:i'), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('document', __('Document / Photo'), ['class' => 'form-label']) }}
                @if($meeting->document && is_array($meeting->document))
                    <div class="mb-2" id="existingDocs">
                        @foreach($meeting->document as $doc)
                            <span class="doc-item d-inline-flex align-items-center mb-1" data-doc="{{ $doc }}">
                                <a href="{{ \App\Models\Utility::get_file('uploads/meetingNew') . '/' . $doc }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-file"></i> {{ $doc }}
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-doc-btn ms-1"
                                        data-doc="{{ $doc }}" title="{{ __('Hapus') }}">
                                    <i class="ti ti-x"></i>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
                <div class="choose-files">
                    <label for="document">
                        <div class="bg-primary document">
                            <i class="ti ti-upload px-1"></i>{{ __('Choose new file (optional)') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control file" name="document[]"
                            id="document" accept="image/*,.pdf,.doc,.docx" multiple
                            data-filename="document_file_name_new"
                            onchange="meetingNewDocPreview(this)">
                        <p class="document_file_name_new mt-2 mb-0"></p>
                        <div id="docFileNames" class="mt-2 mb-0"></div>
                        <div id="docPreview" class="mt-3 d-none">
                            <h6>{{ __('Preview') }}</h6>
                            <div id="docPreviewList"></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                <select class="form-control" name="branch_id" id="branch_id">
                    <option value="">{{ __('Semua Branch') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('employee_id', __('Select Employee (Max 1 per Department)'), ['class' => 'form-label']) }}
                <div class="row" id="employeeChecklist">
                    @include('meetingNew._employee_checklist', ['departments' => $departments, 'employees' => $employees, 'selectedEmployeeIds' => $selectedEmployeeIds])
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}


