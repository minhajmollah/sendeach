@extends('user.layouts.app')
@section('panel')
<section class="mt-3">
    <div class="container-fluid p-0">
	    <div class="row">
	 		<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="responsive-table">
		                <table class="m-0 text-center table--light">
		                    <thead>
		                        <tr>
		                            <th>{{ translate('id') }}</th>
		                            <th>{{ translate('Device ID') }}</th>
		                            <th>{{ translate('Created At') }}</th>
		                        </tr>
		                    </thead>
		                    @forelse($devices as $device)
			                    <tr class="@if($loop->even) table-light @endif">
				                    <td data-label="{{ translate('id') }}">
				                    	{{__($device->id)}}
				                    </td>

				                     <td data-label="{{ translate('Device Id') }}">
				                    	{{__($device->device_id)}}
				                    </td>

                                    <td data-label="{{ translate('Created At') }}">
                                       {{__($device->created_at->format('d-M-Y h:i A'))}}
                                   </td>
			                    </tr>
			                @empty
			                	<tr>
			                		<td class="text-muted text-center" colspan="100%">{{ translate('No Data Found') }}</td>
			                	</tr>
			                @endforelse
		                </table>
	            	</div>
	                <div class="m-3">
	                	{{$devices->appends(request()->all())->links()}}
					</div>
	            </div>
	        </div>
	    </div>
	</div>
</section>


{{-- <div class="modal fade" id="createandroid" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
			<form action="{{route('user.gateway.sms.android.store')}}" method="POST">
				@csrf
	            <div class="modal-body">
	            	<div class="card">
	            		<div class="card-header bg--lite--violet">
	            			<div class="card-title text-center text--light">{{ translate('Add New Android Gateway') }}</div>
	            		</div>
		                <div class="card-body">
							<div class="mb-3">
								<label for="name" class="form-label">{{ translate('Name') }} <sup class="text--danger">*</sup></label>
								<input type="text" class="form-control" id="name" name="name" placeholder="{{ translate('Enter Name')}}
                                " required>
							</div>

							<div class="mb-3">
								<label for="password" class="form-label">{{ translate('Password') }} <sup class="text--danger">*</sup></label>
								<input type="password" class="form-control" id="password" name="password" placeholder="{{ translate('Enter Password')}}" required>
							</div>

							<div class="mb-3">
								<label for="password_confirmation" class="form-label">{{ translate('Confirm Password') }} <sup class="text--danger">*</sup></label>
								<input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ translate('Confirm Password') }}" required>
							</div>

							<div class="mb-3">
								<label for="status" class="form-label">{{ translate('Status')}} <sup class="text--danger">*</sup></label>
								<select class="form-select" name="status" id="status" required>
									<option value="1">{{ translate('Active') }}</option>
									<option value="2">{{ translate('Inactive') }}</option>
								</select>
							</div>
						</div>
	            	</div>
	            </div>

	            <div class="modal_button2">
	                <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
	                <button type="submit" class="bg--success">{{ translate('Submit')}}</button>
	            </div>
	        </form>
        </div>
    </div>
</div>


<div class="modal fade" id="updateandroid" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
			<form action="{{route('user.gateway.sms.android.update')}}" method="POST">
				@csrf
				<input type="hidden" name="id">
	            <div class="modal-body">
	            	<div class="card">
	            		<div class="card-header bg--lite--violet">
	            			<div class="card-title text-center text--light">{{ translate('Update Android Gateway') }}</div>
	            		</div>
		                <div class="card-body">
							<div class="mb-3">
								<label for="name" class="form-label">{{ translate('Name') }}<sup class="text--danger">*</sup></label>
								<input type="text" class="form-control" id="name" name="name" placeholder="{{ translate('Enter Name') }}" required>
							</div>

							<div class="mb-3">
								<label for="password" class="form-label">{{ translate('Password') }} <sup class="text--danger">*</sup></label>
								<input type="password" class="form-control" id="password" name="password" placeholder="{{ translate('Enter Password')}}" required>
							</div>

							<div class="mb-3">
								<label for="status" class="form-label">{{ translate('Status') }} <sup class="text--danger">*</sup></label>
								<select class="form-select" name="status" id="status" required>
									<option value="1">{{ translate('Active') }}</option>
									<option value="2">{{ translate('Inactive') }}</option>
								</select>
							</div>
						</div>
	            	</div>
	            </div>

	            <div class="modal_button2">
	                <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
	                <button type="submit" class="bg--success">{{ translate('Submit') }}</button>
	            </div>
	        </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteandroidApi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('user.gateway.sms.android.delete')}}" method="POST">
				@csrf
				<input type="hidden" name="id">
				<div class="modal_body2">
					<div class="modal_icon2">
						<i class="las la-trash-alt"></i>
					</div>
					<div class="modal_text2 mt-3">
						<h6>{{ translate('Are you sure to want delete this android gateway?') }}</h6>
					</div>
				</div>
				<div class="modal_button2">
					<button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
					<button type="submit" class="bg--danger">{{ translate('Delete') }}</button>
				</div>
			</form>
		</div>
	</div>
</div> --}}
@endsection


{{-- @push('scriptpush')
<script>
	(function($){
		"use strict";
		$('.android').on('click', function(){
			var modal = $('#updateandroid');
			modal.find('input[name=id]').val($(this).data('id'));
			modal.find('input[name=name]').val($(this).data('name'));
			modal.find('input[name=password]').val($(this).data('password'));
			modal.find('select[name=status]').val($(this).data('status'));
			modal.modal('show');
		});

		$('.delete').on('click', function(){
			var modal = $('#deleteandroidApi');
			modal.find('input[name=id]').val($(this).data('id'));
			modal.modal('show');
		});
	})(jQuery);
</script>
@endpush --}}
