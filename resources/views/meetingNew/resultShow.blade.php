@include('meetingNew._info')

    @if($visibleResults->count() > 0)
        <div class="row">
            <div class="col-12 mt-3">
                <h6 class="mb-2">{{ __('Hasil Meeting') }}</h6>
                @foreach($visibleResults as $res)
                    <div class="card mb-2 border">
                        <div class="card-header py-2">
                            <strong>{{ $res->employee->name }}</strong>
                            <small>({{ $res->employee->department ? $res->employee->department->name : '-' }})</small>
                        </div>
                        <div class="card-body">
                            {!! $res->content !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>