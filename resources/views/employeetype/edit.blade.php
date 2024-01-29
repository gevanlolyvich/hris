
{{ Form::model($employee_type, ['route' => ['employeetype.update', $employee_type->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <input type="hidden" name="id" value="{{ $employee_type->id }}">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name')]) }}
                </div>
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('type', $types, null, ['class' => 'form-control select2 ','placeholder' => __('Select Shift Type')]) }}
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
