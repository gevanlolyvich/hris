

{{ Form::model($meeting, ['route' => ['meeting.update', $meeting->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('title', __('Meeting Title'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required' ,'placeholder' => __('Enter Meeting Title')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('meeting_type', __('Meeting Type'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('meeting_type', $meeting_types, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Meeting Type')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('url', __('Meeting URL'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('url', null, ['class' => 'form-control ', 'placeholder' => __('Enter Meeting URL')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('password', __('Meeting Password'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('password', null, ['class' => 'form-control ', 'placeholder' => __('Enter Meeting Password')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('location', null, ['class' => 'form-control ', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('start_time', __('Meeting Start Date'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::datetimeLocal('start_time', null, ['class' => 'form-control datetime-local', 'required' => 'required', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('end_time', __('Meeting End Date'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::datetimeLocal('end_time', null, ['class' => 'form-control datetime-local', 'required' => 'required', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('note', __('Meeting Note'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => '3']) }}
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

