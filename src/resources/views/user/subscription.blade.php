@extends('user.layouts.app')
@section('panel')
<section class="mt-3">
    <div class="container-fluid p-0">
	    <div class="row">
	 		<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="responsive-table">
		                <table class="w-100 m-0 text-center table--light">
		                    <thead>
		                        <tr>
		                           	<th> {{ translate('Date')}}</th>
	                                <th> {{ translate('Amount')}}</th>
									<th> {{ translate('Credits')}}</th>
									<th> {{ translate('Details')}}</th>
	                                <th> {{ translate('Transaction No')}}</th>
		                        </tr>
		                    </thead>
		                    @forelse($credits as $credit)
			                    <tr class="@if($loop->even) table-light @endif">
				                    <td data-label=" {{ translate('Date')}}">
				                    	<span>{{diffForHumans($credit->created_at)}}</span><br>
				                    	{{getDateTime($credit->created_at)}}
				                    </td>

				                    <td data-label=" {{ translate('Amount')}}">
				                    	{{$general->currency_symbol}}{{shortAmount($credit->credit * \App\Models\CreditLog::getDollarPerCredit())}}
				                    </td>

									<td data-label=" {{ translate('Credits')}}">
				                    	{{ $credit->credit }}  {{ translate('Credit')}}
				                    </td>
                                    <td data-label=" {{ translate('Details')}}">
                                        {{ $credit->details }}
                                    </td>
				                    <td data-label=" {{ translate('Transaction Number')}}">
				                    	{{ $credit->trx_number  }}
				                    </td>
			                    </tr>
			                @empty
			                	<tr>
			                		<td class="text-muted text-center" colspan="100%"> {{ translate('No Data Found')}}</td>
			                	</tr>
			                @endforelse
		                </table>
		            </div>
		            <div class="m-3">
						{{$credits->links()}}
					</div>
	            </div>
	        </div>
	    </div>
	</div>
</section>
@endsection







