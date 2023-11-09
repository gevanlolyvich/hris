
{{ Form::model($branch, ['route' => ['branch.update', $branch->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                  
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Branch Name')]) }}
                </div>
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('tolerance', __('Tolerance'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('tolerance', null, ['class' => 'form-control', 'id' => 'tolerance', 'placeholder' => __('Enter Branch Tolerance In Meter')]) }}
                </div>
                @error('tolerance')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('map', __('Map'), ['class' => 'form-label']) }}
                <div id="map"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('latitude', __('Latitude'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('latitude', null, ['class' => 'form-control', 'id' => 'latitude', 'placeholder' => __('Enter Branch Latitude')]) }}
                </div>
                @error('latitude')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('longitude', null, ['class' => 'form-control', 'id' => 'longitude', 'placeholder' => __('Enter Branch Longitude')]) }}
                </div>
                @error('latitude')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    const branchData = ({{ Js::from($branch)}});
    var map = L.map('map').setView([branchData.latitude, branchData.longitude], 14);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> ||' + 
        ' <a href="https://www.openstreetmap.org/fixthemap">Report Missing / Broken Map Data To Open Street Map</a>',
    }).addTo(map);
</script>
