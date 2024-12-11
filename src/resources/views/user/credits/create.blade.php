@extends('user.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row col-6 m-auto">
                <div class="alert alert-success">
                    You Will be just charged 5 dollars per 30 Days to send unlimited messages without watermark.
                </div>
                <div class="alert alert-success">
                    These credits are also used to send Reliable Whatsapp Messages through sendEach Business Gateway.
                    (1 Credit = 1 Whatsapp Message).
                </div>
                <div class="alert alert-info">
                    SendEach Charges $5 per month as service fee. Service fee will be subtracted from deposit immediately.
                </div>
            </div>
            <div class="row d-flex align--center rounded">
                <div class="col-xl-6 m-auto">
                    <div class="card ">
                        <h6 class="card-header">{{ translate('Buy Credits') }}</h6>
                        <div class="card-body">
                            <div class="row">
                                @csrf
                                <div class="col-md-12 mb-4">
                                    <label for="access_token">{{ translate('How much dollars you want to deposit ?')}}
                                        <span
                                            class="text-danger">*</span></label>
                                    <p class="my-2 fw-bold fs--10 text-md-center">1 Credit = ${{ $dollarPerCredit }}</p>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number"
                                               class="form-control @error('price') is-invalid @enderror " min="10"
                                               name="price" id="price" value="{{old('price')}}"
                                               placeholder="{{ translate('Price in Dollars $')}}" aria-label="Amount (to the nearest dollar)" required>
                                        <span class="input-group-text" id="credits"></span>
                                    </div>
                                    @error('price')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                                <p class="fw-bold text-danger fs-9">{{ translate('Minimum $10  of deposit is required.')}} </p>
                                <button type="button" id="buy_credits"
                                        class="btn btn-primary me-sm-3 me-1 float-end w-50 m-auto mt-3">
                                    {{ translate('Buy')}}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="purchase" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Payment Method')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('user.credits.store')}}" method="POST">
                    @csrf
                    <input type="hidden"
                           name="price" value="">
                    <input type="hidden" name="payment_gateway">
                    <div class="modal_body">
                        <div class="container">
                            <div class="col-lg-12">
                                <h6 class="payment-gateway-modal-title">{{ translate('Automatic Payment Method')}}</h6>
                            </div>
                            <div class="col-lg-12">
                                <div class="modal_text2 mt-3">
                                    <div class="mb-3">
                                        <div class="payment-items">
                                            @foreach($paymentMethods as $paymentMethod)
                                                @if(strpos($paymentMethod->unique_code, 'MANUAL') === false)
                                                    <div class="payment-item"
                                                         data-payment_gateway="{{$paymentMethod->id}}">
                                                        <div class="payment-item-img">
                                                            <img
                                                                src="{{showImage(filePath()['payment_method']['path'].'/'.$paymentMethod->image,filePath()['payment_method']['size'])}}"
                                                                alt="{{__($paymentMethod->name)}}">
                                                        </div>
                                                        <h4 class="payment-item-title">
                                                            {{__($paymentMethod->name)}}
                                                        </h4>
                                                        <div class="payment-overlay">
                                                            <button type="submit"
                                                                    class="btn">{{ translate('Process')}}</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <h6 class="payment-gateway-modal-title">{{ translate('Manual Payment Method')}}</h6>
                            </div>
                            <div class="col-lg-12">
                                <div class="modal_text2 mt-3">
                                    <div class="mb-3">
                                        <div class="payment-items">
                                            @foreach($paymentMethods as $paymentMethod)
                                                @if(strpos($paymentMethod->unique_code, 'MANUAL') !== false)
                                                    <div class="payment-item"
                                                         data-payment_gateway="{{__($paymentMethod->id)}}">
                                                        <div class="payment-item-img">
                                                            <img
                                                                src="{{showImage(filePath()['payment_method']['path'].'/'.$paymentMethod->image,filePath()['payment_method']['size'])}}"
                                                                alt="{{__($paymentMethod->name)}}">
                                                        </div>
                                                        <h4 class="payment-item-title">
                                                            {{__($paymentMethod->name)}}
                                                        </h4>
                                                        <div class="payment-overlay">
                                                            <button type="submit"
                                                                    class="btn">{{ translate('Process')}}</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scriptpush')
    <script>
        (function ($) {
            "use strict";
            $("#buy_credits").on('click', function () {
                console.log()
                var modal = $('#purchase');
                modal.find('input[name=price]').val($("#price").val());
                modal.modal('show');
            });

            let creditsPerDollar = {{ $creditsPerDollar }}

            $("#price").on('change', function (){
                $("#credits").text(Math.ceil($("#price").val() * creditsPerDollar)+" Credits")
            })

            $(".payment-item").on('click', function () {
                var modal = $('#purchase');
                modal.find('input[name=payment_gateway]').val($(this).data("payment_gateway"));
            });
        })(jQuery);
    </script>
@endpush


@push('stylepush')
    <style type="text/css">
        .focused-plan {
            border: 1px solid #102078;
        }

        .recommanded {
            position: absolute;
            top: 0;
            left: 0;
            height: 10%;
            width: 100%;
            text-align: right;
        }

        .recommanded span {
            background-color: #102078;
            border-bottom-left-radius: 15px;
        }
    </style>
@endpush
