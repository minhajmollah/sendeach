@extends('admin.layouts.app')
@section('panel')
<section class="mt-3 rounded_box">
	<div class="container-fluid p-0 mb-3 pb-2">
		<div class="row d-flex align--center rounded">
			<div class="col-xl-12">
				<div class="table_heading d-flex align--center justify--between">
                    <nav  aria-label="breadcrumb">
					  	<ol class="breadcrumb">
					    	<li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{translate('Dashboard')}}</a></li>
					    	<li class="breadcrumb-item" aria-current="page"> {{translate('General Setting')}}</li>
					  	</ol>
					</nav>
                </div>
				<div class="card">
					<div class="card-body">
						<form action="{{route('user.general.setting.store')}}" method="POST" enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="mb-3 col-lg-6 col-md-12">
									<label for="sms_gateway" class="form-label">{{translate('SMS Gateway')}} <sup class="text--danger">*</sup></label>
									<select class="form-select" id="sms_gateway" name="sms_gateway" required="">
										<option value="1" @if($general->sms_gateway == 1) selected @endif>{{translate('Api Gateway')}}</option>
										<option value="2" @if($general->sms_gateway == 2) selected @endif>{{translate('Android Gateway')}}</option>
									</select>
								</div>
								<div class="mb-3 col-lg-6 col-md-12">
									<label for="currency_symbol" class="form-label">{{translate('Country Code For Contact')}} <sup class="text--danger">*</sup></label>
									<div class="input-group mb-3">
								  	<label class="input-group-text" for="country_code" id="country--dial--code">{{$general->country_code}}</label>
								  	<select name="country_code" class="form-select" id="country_code">
									    <@foreach($countries as $countryData)
											<option value="{{$countryData->dial_code}}" @if($general->country_code == $countryData->dial_code) selected="" @endif>{{$countryData->country}}</option>
										@endforeach
									  </select>
									</div>
								</div>
							<button type="submit" class="btn btn--primary w-100 text-light">{{translate('Submit')}}</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection


@push('scriptpush')
<script>
	"use strict";
	(function($){
		$('select[name=country_code]').on('change', function(){
			var value = $(this).val();
			$("#country--dial--code").text(value);
		});
	})(jQuery);

</script>
@endpush
