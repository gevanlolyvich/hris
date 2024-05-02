
{{ Form::open(['url' => 'vehicle-officer', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('user', __('Vehicle Officer'), ['class' => 'col-form-label']) }}
                    {{ Form::select('user', $users, null, ['class' => 'form-control select2 user_id', 'placeholder' => __('Select Vehicle Officer')]) }}
                </div>
            </div>
            @if ($full_access)
                <div class="col-12">
                    <div class="form-group">
                        {{ Form::label('is_resricted', __('Access'), ['class' => 'col-form-label']) }}
                        {{ Form::select('is_resricted', $access, null, ['class' => 'form-control select2 access', 'id' => 'is_resricted', 'placeholder' => __('Select Access')]) }}
                    </div>
                </div>
            @else
                <div class="col-12">
                    <div class="form-group">
                        {{ Form::hidden('is_resricted', true, ['id' => 'is_resricted']) }}
                    </div>
                </div>
            @endif
            <div class="col-12" id="branch_div" style="display: none">
                <div class="form-group">
                    {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
                    {{ Form::select('branch_id', $branches, [], ['class' => 'form-control select2 branch_id', 'data-placeholder' => __('Select Branch'), 'multiple' => true, 'name' => 'branch_id[]']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}