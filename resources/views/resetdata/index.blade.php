@extends('layouts.master')
@section('heading')
    {{ __('Reset Data')}}
@stop

@section('content')
<div class="row">
    <form action="{{ route('resetdata.reset') }}" method="GET">
        <div class="col-lg-12">
            <div class="sidebarheader"><p>{{ __('Overall Settings') }}</p></div>
        </div>

        <div class="col-lg-12">
            <div class="form-group">
                <div class="tablet movedown">
                    <div class="tablet__head slim">
                        <div class="tablet__head-label">
                            <h3 class="tablet__head-title">@lang('Reinitialiser les donnéees')</h3>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <input type="submit" class="btn btn-md btn-brand" value="@lang('Reset data')">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- END OF THE EDIT MODAL SECTION -->
@stop

