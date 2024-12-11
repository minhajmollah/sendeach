@extends('user.layouts.app')
@section('panel')
<section class="mt-3">
    <div class="container-fluid p-0">
	    <div class="row">
	    	<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="card-body">
	                    <form action="/{{ request()->path() }}" method="GET">
	                        <div class="row align-items-center">
	                            <div class="col-lg-5">
	                                <label>{{ translate('By Contacts')}}</label>
	                                <input type="text" autocomplete="off" name="search" value="{{ request('search') }}" placeholder="{{ translate('Search with contacts number')}}" class="form-control" id="search">
	                            </div>
	                            <div class="col-lg-5">
	                                <label>{{ translate('By Date')}}</label>
	                                <input type="text" class="form-control datepicker-here" name="date" value="{{ request('date') }}" data-range="true" data-multiple-dates-separator=" - " data-language="en" data-position="bottom right" autocomplete="off" placeholder="{{ translate('From Date-To Date')}}" id="date">
	                            </div>
	                            <div class="col-lg-2">
	                                <button class="btn btn--primary w-100 h-45 mt-4" type="submit">
	                                    <i class="fas fa-search"></i> {{ translate('Search')}}
	                                </button>
	                            </div>
	                        </div>
	                    </form>
	                </div>
	            </div>
	        </div>

            <div class="d-flex col-lg-4 gap-2 col-sm-12 col-md-6 justify-content-start">
                <div class="">
                    <button class="btn btn--danger delete-log"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Delete">{{translate('Delete')}}</button>
                </div>
                <div class="">
                    <button class="btn btn--danger delete-log"
                            type="submit" data-bs-toggle="tooltip"
                            data-bs-placement="top" data-delete-all="true"
                            title="Delete All">{{ translate('Delete All') }}</button>
                </div>
            </div>

	 		<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="responsive-table">
		                <table class="m-0 text-center table--light">
		                    <thead>
		                        <tr>
                                    <th class="d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-2 checkAll"
                                               type="checkbox"
                                               value=""
                                               aria-label="Checkbox for following text input"> {{ translate('#')}}
                                    </th>
                                    <th>{{ translate('To')}}</th>
{{--		                            <th>{{ translate('Credit')}}</th>--}}
		                            <th>{{ translate('Initiated') }}</th>
		                            <th>{{ translate('Status') }}</th>
		                            <th>{{ translate('Action') }}</th>
		                        </tr>
		                    </thead>
		                    @forelse($logs as $smsLog)
			                    <tr class="@if($loop->even) table-light @endif">
                                    <td class="d-none d-md-flex align-items-center">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" name="logId"
                                               value="{{$smsLog->id}}"
                                               aria-label="Checkbox for following text input">
                                        {{$loop->iteration}}
                                    </td>

				                    <td data-label="{{ translate('To')}}">
				                    	{{Str::limit($smsLog->to, 80)}}
				                    </td>

{{--				                     <td data-label="{{ translate('Credit')}}">--}}
{{--				                    	@php--}}
{{--									        $messages = str_split($smsLog->message,160);--}}
{{--									        $totalMessage = count($messages);--}}
{{--									    @endphp--}}
{{--									    {{$totalMessage}} {{ translate('Credit')}}--}}
{{--				                    </td>--}}

				                    <td data-label="{{ translate('Initiated')}}">
				                    	{{getDateTime($smsLog->initiated_time)}}
				                    </td>

				                    <td data-label="{{ translate('Status')}}">
				                    	@if($smsLog->status == 1)
				                    		<span class="badge badge--primary">{{ translate('Pending ')}}</span>
				                    	@elseif($smsLog->status == 2)
				                    		<span class="badge badge--info">{{ translate('Schedule')}}</span>
				                    	@elseif($smsLog->status == 3)
				                    		<span class="badge badge--danger">{{ translate('Fail')}}</span>
                                        @elseif($smsLog->status == 5)
                                            <span class="badge badge--warning">{{ translate('Processing')}}</span>
				                    	@else
				                    		<span class="badge badge--success">{{ translate('Delivered')}}</span>
				                    	@endif
				                    </td>

				                    <td data-label={{ translate('Action')}}>
			                    		<a class="btn--primary text--light details"
			                    		data-message="{{$smsLog->message}}"
			                    		data-to="{{$smsLog->to}}"
			                    		data-bs-placement="top" title="Details"
			                    		data-bs-toggle="modal"
			                    		data-bs-target="#smsdetails"
				                    		><i class="las la-desktop"></i></a>
				                    </td>
			                    </tr>
			                @empty
			                	<tr>
			                		<td class="text-muted text-center" colspan="100%">{{ translate('No Data Found')}}</td>
			                	</tr>
			                @endforelse
		                </table>
	            	</div>
	                <div class="m-3">
	                	{{$logs->appends(request()->all())->links()}}
					</div>
	            </div>
	        </div>
	    </div>
	</div>
</section>


<div class="modal fade" id="smsdetails" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
            	<div class="card">
            		<div class="card-header bg--lite--violet">
            			<div class="card-title text-center text--light">{{ translate('Message')}}</div>
            		</div>
        			<div class="card-body mb-3">
        				<p id="message--text"></p>
                        <p class="mt-4">
                            <span class="fw-bold">Recipients: </span>
                            <div class="text-muted small" id="recipients"></div>
                        </p>
        			</div>
        		</div>
        	</div>

            <div class="modal_button2">
                <button type="button" class="w-100" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.sms.delete') }}"
                  method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id">
                <div class="modal_body2">
                    <div class="modal_icon2">
                        <i class="las la-trash-alt"></i>
                    </div>
                    <div class="modal_text2 mt-3">
                        <h6>{{ translate('Are you sure to delete this message from log')}}</h6>
                        <p class="text-muted small">Note: There is no guarantee that pending or processing messages
                            will not be sent, even if their logs are deleted.</p>
                    </div>
                </div>
                <div class="modal_button2">
                    <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                    <button type="submit" class="bg--danger">{{ translate('Delete')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@push('scriptpush')
<script>
	(function($){
		"use strict";
		$('.details').on('click', function(){
			var modal = $('#smsdetails');
			var message = $(this).data('message');
			$("#message--text").text(message);
			$("#recipients").text($(this).data('to'));
			modal.modal('show');
		});
	})(jQuery);

    $('.delete-log').on('click', function () {
        let modal = $('#delete');

        let ids = [];
        $("input:checkbox[name=logId]:checked").each(function () {
            ids.push($(this).val());
        });

        let data = $(this).data('delete_id')
        data ? ids.push(data) : null

        console.log(data)

        if ($(this).data('delete-all')) {
            ids = '{{  \Illuminate\Support\Arr::last(explode('/', request()->path())) }}'
        }

        modal.find('input[name=id]').val(ids);
        modal.modal('show');
    });

    $('.checkAll').click(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);

        let ids = [];
        $("input:checkbox[name=whatsappid]:checked").each(function () {
            ids.push($(this).val());
        });

    });
</script>
@endpush
