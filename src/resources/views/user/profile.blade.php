@extends('user.layouts.app')
@section('panel')
<section class="mt-3 rounded_box">
	<div class="container-fluid p-0 mb-3 pb-2">
		<div class="row d-flex align--center rounded">
			<div class="col-xl-3 col-lg-4 mb-30">
	            <div class="card b-radius-5 overflow-hidden profile-card">
	                <div class="card-body p-0">
	                    <div class="d-flex p-2 bg--lite--violet align-items-center">
	                        <div class="avatar avatar--lg">
	                            <img src="{{showImage('assets/images/user/profile/'.$user->image)}}" alt="Image">
	                        </div>
	                        <div class="pl-2">
	                            <h5 class="text--light m-0 p-0">{{__($user->name)}}</h5>
	                        </div>
	                    </div>
	                    <ul class="list-group">
	                        <li class="list-group-item d-flex justify-content-between align-items-center">
	                            {{ translate('Name')}}<span class="font-weight-bold">{{__($user->name)}}</span>
	                        </li>

	                        <li class="list-group-item d-flex justify-content-between align-items-center">
	                            {{ translate('Email')}}<span class="font-weight-bold">{{__($user->email)}}</span>
	                        </li>
	                    </ul>
	                </div>
	            </div>
	        </div>

			<div class="col-xl-9 col-lg-8">
				<div class="card">
					<div class="card-body">
						<form action="{{route('user.profile.update')}}" method="POST" enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="mb-3 col-12 col-md-6">
									<label for="name" class="form-label">{{ translate('Name')}}</label>
									<input type="name" class="form-control" id="name" value="{{$user->name}}" placeholder="{{ translate('Enter Name')}}" name="name" required="">
								</div>

								<div class="mb-3 col-12 col-md-6">
									<label for="email" class="form-label">{{ translate('Email')}}</label>
									<input type="email" class="form-control" id="email" value="{{$user->email}}" placeholder="{{ translate('Enter Email')}}" name="email" aria-describedby="emailHelp">
								</div>

								<div class="mb-3 col-12 col-md-12">
									<label for="phone" class="form-label">{{ translate('WhatsApp Phone')}}</label>
									<input type="text" class="form-control" id="phone" value="{{$user->phone}}" name="phone" inputmode="numeric" pattern="[0-9]+" aria-describedby="phoneHelp" placeholder="{{ translate('Enter Phone no.')}}" required>
                                    <small id="phoneHelp" style="font-size: 0.7em;">Enter number without dashes, spaces, or brackets. e.g. 1876123456</small>
								</div>

								<div class="mb-3 col-12 col-md-12">
									<label for="address" class="form-label">{{ translate('Address')}}</label>
									<input type="text" class="form-control" id="address" value="{{@$user->address->address}}" name="address" placeholder="{{ translate('Enter Address')}}" aria-describedby="emailHelp">
								</div>

								<div class="mb-3 col-12 col-md-6">
									<label for="city" class="form-label">{{ translate('City')}}</label>
									<input type="text" class="form-control" id="city" value="{{@$user->address->city}}" name="city" placeholder="{{ translate('Enter City')}}" aria-describedby="emailHelp">
								</div>

								<div class="mb-3 col-12 col-md-6">
									<label for="state" class="form-label">{{ translate('State')}}</label>
									<input type="text" class="form-control" id="state" value="{{@$user->address->state}}" name="state" placeholder="{{ translate('Enter State')}}" aria-describedby="emailHelp">
								</div>

								<div class="mb-3 col-12 col-md-6">
									<label for="zip" class="form-label">{{ translate('Zip')}}</label>
									<input type="text" class="form-control" id="zip" value="{{@$user->address->zip}}" name="zip" placeholder="{{ translate('Enter Zip')}}" aria-describedby="emailHelp">
								</div>

                                <div class="mb-3 col-lg-6 col-md-12">
                                    <label for="timezone" class="form-label">{{translate('Time Zone')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" id="timezone" name="timezone" required="">
                                        @foreach(timezone_identifiers_list() as $timeLocation)
                                            <option value='{{ @$timeLocation}}'
                                                    @if(@$user->timezone == $timeLocation) selected @endif>{{__($timeLocation)}}</option>
                                        @endforeach
                                    </select>
                                </div>

								<div class="mb-3 col-12 col-md-6">
									<label for="image" class="form-label">{{ translate('Image')}}</label>
									<input type="file" class="form-control" id="image" name="image">
								</div>
							</div>
							<button type="submit" class="btn btn--primary w-100 text-light">{{ translate('Submit')}}</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
