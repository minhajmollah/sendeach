@extends('user.layouts.app')
@section('panel')
<section class="mt-3">
        @if(auth()->user()->email == '')
            <div class="alert alert-info">
                You Email is missing in your profile, Please update your profile.
            </div>
        @endif
        <div class="rounded_box">
            <div class="parent_pinned_project" style="margin-bottom: 1rem;">
                <a href="javascript:void(0);" class="single_pinned_project shadow">
                    <div class="pinned_icon">
                        <i class="las la-credit-card"></i>
                    </div>
                    <div class="pinned_text">
                        <div>
                            <h6 class="text-secondary">{{ translate('Remaining Credits')}}</h6>
                            <h4 class="fw-bold text-secondary">{{auth()->user()->credit ?: 0}}</h4>
                        </div>
                    </div>
                </a>
                <a href="javascript:void(0);" class="single_pinned_project shadow">
                    <div class="pinned_icon">
                        <i class="la-credit-card-alt la"></i>
                    </div>
                    <div class="pinned_text">
                        <div>
                            <h6 class="text-secondary">{{ translate('Remaining OpenAI Tokens')}}</h6>
                            <h4 class="fw-bold text--secondary">{{$availableTrailTokens ?? 0}}</h4>
                        </div>
                    </div>
                </a>
                <a href="javascript:void(0);" class="single_pinned_project shadow">
                    <div class="pinned_icon">
                        <i class="las la-phone"></i>
                    </div>
                    <div class="pinned_text">
                        <div>
                            <h6 class="text--secondary">{{ translate('Total Phone Contacts')}}</h6>
                            <h4 class="fw-bold text--warning">{{ \App\Models\Contact::where('user_id', auth()->id())->count() }}</h4>
                        </div>
                    </div>
                </a>
                <a href="javascript:void(0);" class="single_pinned_project shadow">
                    <div class="pinned_icon">
                        <i class="lab la-facebook-messenger"></i>
                    </div>
                    <div class="pinned_text">
                        <div>
                            <h6 class="text-secondary">FB Messenger Bot Status</h6>
                            <h4 class="fw-bold {{ $facebookMessenger?->status ? 'text--success' : 'text--danger' }}">{{ $facebookMessenger?->status ? 'Active' : 'In Active' }}</h4>
                        </div>
                    </div>
                </a>

                @if(config('app.modules.sms'))
                    <a href="{{route('user.sms.campaign')}}" class="single_pinned_project shadow">
                        <div class="pinned_icon">
                            <i class="las la-comment"></i>
                        </div>
                        <div class="pinned_text">
                            <div>
                                <h6 class="text-secondary">{{ translate('Total SMS')}}</h6>
                                <h4 class="fw-bold text-info">{{$smslog['all']}}</h4>
                            </div>
                        </div>
                    </a>
                @endif

                @if(config('app.modules.email'))
                    <a href="{{route('user.manage.email.campaign')}}" class="single_pinned_project shadow">
                        <div class="pinned_icon">
                            <i class="las la-envelope"></i>
                        </div>
                        <div class="pinned_text">
                            <div>
                                <h6 class="text--secondary">{{ translate('Total Email')}}</h6>
                                <h4 class="fw-bold text-secondary">{{$emailLog['all']}}</h4>
                            </div>
                        </div>
                    </a>

                @endif

                <a href="{{route('user.whatsapp.campaign')}}" class="single_pinned_project shadow">
                    <div class="pinned_icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="pinned_text">
                        <div>
                            <h6 class="text--secondary">{{ translate('Total WhatsApp Message')}}</h6>
                            <h4 class="fw-bold text--primary">{{$whatsappLog['all']}}</h4>
                        </div>
                    </div>
                </a>

                <a href="javascript:void(0);" class="single_pinned_project shadow">
                    <div class="pinned_icon">
                        <i class="la-user-friends la"></i>
                    </div>
                    <div class="pinned_text">
                        <div>
                            <h6>{{ translate('Total Email Contacts')}}</h6>
                            <h4 class="fw-bold text-dark">{{ \App\Models\EmailContact::where('user_id', auth()->id())->count() }}</h4>
                        </div>
                    </div>
                </a>
            </div>
        </div>
</section>


<section class="mt-3">
    <div class="rounded_box">
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-6 p-1">
                <h6 class="header-title fw-bold">{{ translate('Active Whatsapp Web Devices')}}</h6>
                <div class="responsive-table">
                    <table class="m-0 text-center table--light datatable">
                        <thead>
                            <tr>
                                <th>{{ translate('Name')}}</th>
                                <th>{{ translate('Number')}}</th>
                                <th>{{ translate('Updated On')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($whatsappDevices['web'] as $whatsappDevice)
                            <tr class="@if($loop->even) table-light @endif">
                                <td data-label="{{ translate('Name')}}">
                                    {{ $whatsappDevice->name }}
                                </td>

                                <td data-label="{{ translate('Number')}}">
                                    {{ $whatsappDevice->number }}
                                </td>

                                <td data-label="{{ translate('updated_at')}}">
                                    {{ $whatsappDevice->updated_at->toFormattedDateString() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ translate('No Data Found')}}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 col-lg-12 col-xl-6 p-1">
                <h6 class="header-title fw-bold">{{ translate('Active Whatsapp PC Devices')}}</h6>
                <div class="responsive-table">
                    <table class="m-0 text-center table--light datatable">
                        <thead>
                        <tr>
                            <th>{{ translate('Device ID')}}</th>
                            <th>{{ translate('Connected On')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($whatsappDevices['desktop'] as $whatsappDevice)
                            <tr class="@if($loop->even) table-light @endif">

                                <td data-label="{{ translate('Device ID')}}">
                                    {{ $whatsappDevice->device_id }}
                                </td>

                                <td data-label="{{ translate('connected on')}}">
                                    {{ $whatsappDevice->updated_at->toFormattedDateString() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ translate('No Data Found')}}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>


@endsection


@push('scriptpush')
@if(config('app.modules.sms'))
<script>
    "use strict";

    let earning = document.getElementById('earning').getContext('2d');
    const myChart2 = new Chart(earning, {
        type: 'bar',
        data: {
            labels: [@php echo "'".implode("', '", $smsReport['month']->toArray())."'" @endphp],
            datasets: [{
                label: '# {{ translate('Total SMS Send')}}',
                barThickness: 10,
                minBarLength: 2,
                data: [{{implode(",",$smsReport['month_sms']->toArray())}}],
                backgroundColor: [
                    'rgba(255, 99, 132)',
                    'rgba(54, 162, 235)',
                    'rgba(255, 206, 86)',
                    'rgba(75, 192, 192)',
                    'rgba(153, 102, 255)',
                    'rgba(255, 159, 64)',
                    'rgba(255, 99, 132)',
                    'rgba(54, 162, 235)',
                    'rgba(255, 206, 86)',
                    'rgba(75, 192, 192)',
                    'rgba(153, 102, 255)',
                    'rgba(255, 159, 64)',
                    'rgba(255, 99, 132)',
                    'rgba(54, 162, 235)',
                    'rgba(255, 206, 86)',
                    'rgba(75, 192, 192)',
                    'rgba(153, 102, 255)',
                    'rgba(255, 159, 64)',
                    'rgba(255, 99, 132)',
                    'rgba(54, 162, 235)',
                    'rgba(255, 206, 86)',
                    'rgba(75, 192, 192)',
                    'rgba(153, 102, 255)',
                    'rgba(255, 159, 64)'
                ]
            }]
        },
        options: {
            responsive: true,
    }
});
</script>
@endif
@endpush
