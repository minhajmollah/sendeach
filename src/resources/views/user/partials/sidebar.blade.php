<div id="sideContent" class="side_content">
    <div class="logo_container">
        <div class="logo_name">
            <div class="logo_img">
                <img src="{{ showImage(filePath()['site_logo']['path'] . '/site_logo.png') }}"
                    alt="{{ translate('Site Logo') }}">
                <div onclick="showSideBar()" class="cross">
                    <i class="lar la-times-circle fs--9 text--light"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="side_bar_menu_container">
        <div class="side_bar_menu_list">
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive('user.dashboard') }}"
                        href="{{ route('user.dashboard') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 las la-home text--light me-2"></i></span>{{ translate('Dashboard') }}
                        </div>
                    </a>
                </li>
            </ul>
            <h1 class="text--light m-2">{{ translate('Group & Contacts') }}</h1>
            <ul>
                <li>
                    <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.phone.book.group.index', 'user.phone.book.contact.index', 'user.phone.book.template.index', 'user.phone.book.sms.contact.group']) }} side_bar_twenty_list"
                        href="javascript:void(0)">
                        <div><span class="me-4"><i
                                    class="fs-5 las la-sms text--light"></i></span>{{ translate('Number Contacts') }}
                        </div>
                        <i class="las la-angle-down icon20"></i>
                    </a>
                    <ul class="first_twenty_child {{ menuActive('user.phone.book*', 20) }}">
                        <li>
                            <a class="{{ menuActive(['user.phone.book.group.index', 'user.phone.book.sms.contact.group']) }}"
                                href="{{ route('user.phone.book.group.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Groups') }}</a>
                            <a class="{{ menuActive('user.phone.book.contact.index') }}"
                                href="{{ route('user.phone.book.contact.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Contacts') }}</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.phone.book.group.index', 'user.phone.book.contact.index', 'user.phone.book.template.index', 'user.phone.book.sms.contact.group']) }} side_bar_two_list"
                        href="javascript:void(0)">
                        <div><span class="me-4"><i
                                    class="fs-5 las la-sms text--light"></i></span>{{ translate('Number Contacts') }}
                        </div>
                        <i class="las la-angle-down icon20"></i>
                    </a>
                    <ul class="first_two_child {{ menuActive('user.phone.book*', 20) }}">
                        <li>
                            <a class="{{ menuActive(['user.phone.book.group.index', 'user.phone.book.sms.contact.group']) }}"
                                href="{{ route('user.phone.book.group.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Groups') }}</a>
                            <a class="{{ menuActive('user.phone.book.contact.index') }}"
                                href="{{ route('user.phone.book.contact.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Contacts') }}</a>
                        </li>
                    </ul>
                </li>



                <li>
                    <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.email.group.index', 'user.email.contact.index', 'user.email.contact.group']) }} side_bar_eight_list"
                        href="javascript:void(0)">
                        <div><span class="me-4"><i
                                    class="fs-5 las la-envelope text--light"></i></span>{{ translate('Email Contacts') }}
                        </div>
                        <i class="las la-angle-down icon8"></i>
                    </a>
                    <ul class="first_eight_child {{ menuActive('user.email*', 8) }}">
                        <li>
                            <a class="{{ menuActive(['user.email.group.index', 'user.email.contact.group']) }}"
                                href="{{ route('user.email.group.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Groups') }}</a>
                            <a class="{{ menuActive('user.email.contact.index') }}"
                                href="{{ route('user.email.contact.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Contacts') }}</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <h1 class="text--light m-2">{{ translate('Messaging & Mail') }}</h1>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive('user.sms.send') }}"
                        href="{{ route('user.sms.send') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 las la-inbox text--light me-2"></i></span>{{ translate('Send SMS') }}
                        </div>
                    </a>
                </li>
                @php
                    
                    $sendWhatsappRoute = match (auth()->user()->default_whatsapp_gateway) {
                        \App\Models\WhatsappLog::GATEWAY_BUSINESS => 'user.business.whatsapp.sendeach_create',
                        \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN => 'user.business.whatsapp.create',
                        \App\Models\WhatsappLog::GATEWAY_DESKTOP => 'user.desktop.whatsapp.send',
                        default => 'user.whatsapp.send',
                    };
                    
                @endphp
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive($sendWhatsappRoute) }}"
                        href="{{ route($sendWhatsappRoute) }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 lab la-whatsapp text--light me-1"></i></span>{{ translate('Send Whatsapp') }}
                        </div>
                    </a>
                </li>
                {{--                <li> --}}
                {{--                    <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.business.whatsapp.create']) }} side_bar_twenty_five_list" --}}
                {{--                       href="javascript:void(0)"> --}}
                {{--                        <div><span class="me-3"><i class="fs-5 lab la-whatsapp text--light me-1"></i></span> --}}
                {{--                            {{ translate('Send Whatsapp') }}</div> --}}
                {{--                        <i class="las la-angle-down icon25"></i> --}}
                {{--                    </a> --}}
                {{--                    <ul class="first_twenty_five_child {{ menuActive(['user.business.whatsapp*', 'user.whatsapp.send'], 25) }}"> --}}
                {{--                        <li> --}}
                {{--                            <a class="{{ menuActive('user.business.whatsapp.create') }}" --}}
                {{--                               href="{{route('user.business.whatsapp.create')}}"> --}}
                {{--                                <div> --}}
                {{--                                <span class="me-3"><i --}}
                {{--                                        class="lab la-jira me-2"></i></span>{{ translate('Via Own Business Channel') }} --}}
                {{--                                </div> --}}
                {{--                            </a> --}}
                {{--                        </li> --}}
                {{--                        <li> --}}
                {{--                            <a class="{{ menuActive('user.business.whatsapp.sendeach_create') }}" --}}
                {{--                               href="{{route('user.business.whatsapp.sendeach_create')}}"> --}}
                {{--                                <div> --}}
                {{--                                <span class="me-3"><i --}}
                {{--                                        class="lab la-jira me-2"></i></span>{{ translate('Via SendEach Business Channel') }} --}}
                {{--                                </div> --}}
                {{--                            </a> --}}
                {{--                        </li> --}}
                {{--                        <li> --}}
                {{--                            <a class="{{ menuActive('user.whatsapp.send') }}" --}}
                {{--                               href="{{route('user.whatsapp.send')}}"> --}}
                {{--                                <div> --}}
                {{--                                <span class="me-3"><i --}}
                {{--                                        class="lab la-jira me-2"></i></span>{{ translate('Via Web Channel') }} --}}
                {{--                                </div> --}}
                {{--                            </a> --}}
                {{--                        </li> --}}

                {{--                    </ul> --}}
                {{--                </li> --}}


                {{--                <li class="side_bar_list d--flex align--center"> --}}
                {{--                    <a class="ms--1 d--flex align--center {{ menuActive('user.business.whatsapp.create') }}" --}}
                {{--                       href="{{ route('user.business.whatsapp.create') }}"> --}}
                {{--                        <div> --}}
                {{--                            <span class="me-3"><i --}}
                {{--                                    class="fs-5 lab la-whatsapp text--light me-2"></i></span>{{ translate('Send Business WhatsApp') }} --}}
                {{--                        </div> --}}
                {{--                    </a> --}}
                {{--                </li> --}}

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive('user.manage.email.send') }}"
                        @if (auth()->user()->email) href="{{ route('user.manage.email.send') }}" @else href="#"
                       data-bs-toggle="modal" data-bs-target="#confirmEmailModel" @endif>
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 las la-envelope-open-text me-4"></i>{{ translate('Send Email') }}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive('user.template.index') }}"
                        href="{{ route('user.template.index') }}">
                        <div>
                            <span class="me-3"><i
                                    class="las la-braille fs-5 text--light me-2"></i></span>{{ translate('Messaging Template') }}
                        </div>
                    </a>
                </li>
            </ul>

            <h1 class="text--light m-2">{{ translate('Campaign Reports') }}</h1>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.sms.campaign']) }}"
                        href="{{ route('user.sms.campaign') }}">
                        <div>
                            <span class="me-4"><i class="fs-5 las la-inbox text--light"></i></span>
                            {{ translate('SMS Campaign') }}
                        </div>
                    </a>
                </li>

                @if (
                    !auth()->user()->default_whatsapp_gateway ||
                        auth()->user()->default_whatsapp_gateway == \App\Models\WhatsappLog::GATEWAY_WEB)
                    {{--                    <li> --}}
                    {{--                        <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.whatsapp.index', 'user.whatsapp.pending', 'user.whatsapp.schedule', 'user.whatsapp.delivered', 'user.whatsapp.failed', 'user.whatsapp.search', 'user.whatsapp.date.search', 'user.whatsapp.processing']) }} side_bar_twenty_three_list" --}}
                    {{--                           href="javascript:void(0)"> --}}
                    {{--                            <div><span class="me-4"><i --}}
                    {{--                                        class="fs-5 fab fa-whatsapp text--light"></i></span>{{ translate('WhatsApp Reports') }} --}}
                    {{--                            </div> --}}
                    {{--                            <i class="las la-angle-down icon23"></i> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="first_twenty_three_child {{ menuActive(['user.whatsapp.index', 'user.whatsapp.pending', 'user.whatsapp.schedule', 'user.whatsapp.delivered', 'user.whatsapp.failed', 'user.whatsapp.search', 'user.whatsapp.date.search', 'user.whatsapp.processing'], 23) }}"> --}}
                    {{--                            <li> --}}
                    {{--                                <a class="{{ menuActive('user.whatsapp.index') }}" --}}
                    {{--                                   href="{{ route('user.whatsapp.index') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('All Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.whatsapp.pending') }}" --}}
                    {{--                                   href="{{ route('user.whatsapp.pending') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Pending Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.whatsapp.schedule') }}" --}}
                    {{--                                   href="{{ route('user.whatsapp.schedule') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Schedule Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.whatsapp.processing') }}" --}}
                    {{--                                   href="{{ route('user.whatsapp.processing') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Processing Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.whatsapp.delivered') }}" --}}
                    {{--                                   href="{{ route('user.whatsapp.delivered') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Delivered Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.whatsapp.failed') }}" --}}
                    {{--                                   href="{{ route('user.whatsapp.failed') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Failed Message') }}</a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --}}
                    {{--                    </li> --}}

                    <li class="side_bar_list d--flex align--center">
                        <a class="ms--1 d--flex align--center {{ menuActive(['user.whatsapp.campaign']) }}"
                            href="{{ route('user.whatsapp.campaign') }}">
                            <div>
                                <span class="me-3"><i class="fs-5 fab fa-whatsapp text--light me-2"></i></span>
                                {{ translate('Whatsapp Campaign') }}
                            </div>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->default_whatsapp_gateway == \App\Models\WhatsappLog::GATEWAY_DESKTOP)
                    {{--                    <li> --}}
                    {{--                        <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.desktop.whatsapp.index', 'user.desktop.whatsapp.pending', 'user.desktop.whatsapp.schedule', 'user.desktop.whatsapp.delivered', 'user.desktop.whatsapp.failed', 'user.desktop.whatsapp.search', 'user.desktop.whatsapp.date.search', 'user.desktop.whatsapp.processing']) }} side_bar_twenty_three_list" --}}
                    {{--                           href="javascript:void(0)"> --}}
                    {{--                            <div><span class="me-4"><i --}}
                    {{--                                        class="fs-5 fab fa-whatsapp text--light"></i></span>{{ translate('WhatsApp Reports') }} --}}
                    {{--                            </div> --}}
                    {{--                            <i class="las la-angle-down icon23"></i> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="first_twenty_three_child {{ menuActive(['user.desktop.whatsapp.index', 'user.desktop.whatsapp.pending', 'user.desktop.whatsapp.schedule', 'user.desktop.whatsapp.delivered', 'user.desktop.whatsapp.failed', 'user.desktop.whatsapp.search', 'user.desktop.whatsapp.date.search', 'user.desktop.whatsapp.processing'], 23) }}"> --}}
                    {{--                            <li> --}}
                    {{--                                <a class="{{ menuActive('user.desktop.whatsapp.index') }}" --}}
                    {{--                                   href="{{ route('user.desktop.whatsapp.index') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('All Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.desktop.whatsapp.pending') }}" --}}
                    {{--                                   href="{{ route('user.desktop.whatsapp.pending') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Pending Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.desktop.whatsapp.schedule') }}" --}}
                    {{--                                   href="{{ route('user.desktop.whatsapp.schedule') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Schedule Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.desktop.whatsapp.processing') }}" --}}
                    {{--                                   href="{{ route('user.desktop.whatsapp.processing') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Processing Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.desktop.whatsapp.delivered') }}" --}}
                    {{--                                   href="{{ route('user.desktop.whatsapp.delivered') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Delivered Message') }}</a> --}}
                    {{--                                <a class="{{ menuActive('user.desktop.whatsapp.failed') }}" --}}
                    {{--                                   href="{{ route('user.desktop.whatsapp.failed') }}"><i --}}
                    {{--                                        class="lab la-jira me-3"></i>{{ translate('Failed Message') }}</a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --}}
                    {{--                    </li> --}}

                    <li class="side_bar_list d--flex align--center">
                        <a class="ms--1 d--flex align--center {{ menuActive(['user.desktop.whatsapp.campaign']) }}"
                            href="{{ route('user.desktop.whatsapp.campaign') }}">
                            <div>
                                <span class="me-3"><i class="fs-5 fab fa-whatsapp text--light me-2"></i></span>
                                {{ translate('Whatsapp Campaign') }}
                            </div>
                        </a>
                    </li>
                @endif

                @if (in_array(auth()->user()->default_whatsapp_gateway, [
                        \App\Models\WhatsappLog::GATEWAY_BUSINESS,
                        \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN,
                    ]))
                    <li>
                        <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.business.whatsapp.index', 'user.business.whatsapp.pending', 'user.business.whatsapp.schedule', 'user.business.whatsapp.delivered', 'user.business.whatsapp.failed', 'user.business.whatsapp.search', 'user.business.whatsapp.date.search', 'user.business.whatsapp.processing']) }} side_bar_twenty_four_list"
                            href="javascript:void(0)">
                            <div><span class="me-4"><i
                                        class="fs-5 fab fa-whatsapp text--light"></i></span>{{ translate('WhatsApp Business Reports') }}
                            </div>
                            <i class="las la-angle-down icon24"></i>
                        </a>
                        <ul
                            class="first_twenty_four_child {{ menuActive(['user.business.whatsapp.index', 'user.business.whatsapp.pending', 'user.business.whatsapp.schedule', 'user.business.whatsapp.delivered', 'user.business.whatsapp.failed', 'user.business.whatsapp.search', 'user.business.whatsapp.date.search', 'user.business.whatsapp.processing'], 24) }}">
                            <li>
                                <a class="{{ menuActive('user.business.whatsapp.index') }}"
                                    href="{{ route('user.business.whatsapp.index') }}"><i
                                        class="lab la-jira me-3"></i>{{ translate('All Message') }}</a>
                                <a class="{{ menuActive('user.business.whatsapp.pending') }}"
                                    href="{{ route('user.business.whatsapp.pending') }}"><i
                                        class="lab la-jira me-3"></i>{{ translate('Pending Message') }}</a>
                                <a class="{{ menuActive('user.business.whatsapp.schedule') }}"
                                    href="{{ route('user.business.whatsapp.schedule') }}"><i
                                        class="lab la-jira me-3"></i>{{ translate('Schedule Message') }}</a>
                                <a class="{{ menuActive('user.business.whatsapp.processing') }}"
                                    href="{{ route('user.business.whatsapp.processing') }}"><i
                                        class="lab la-jira me-3"></i>{{ translate('Processing Message') }}</a>
                                <a class="{{ menuActive('user.business.whatsapp.delivered') }}"
                                    href="{{ route('user.business.whatsapp.delivered') }}"><i
                                        class="lab la-jira me-3"></i>{{ translate('Delivered Message') }}</a>
                                <a class="{{ menuActive('user.business.whatsapp.failed') }}"
                                    href="{{ route('user.business.whatsapp.failed') }}"><i
                                        class="lab la-jira me-3"></i>{{ translate('Failed Message') }}</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.manage.email.campaign']) }}"
                        href="{{ route('user.manage.email.campaign') }}">
                        <div>
                            <span class="me-4"><i class="fs-5 las la-envelope-open-text text--light"></i></span>
                            {{ translate('Email Campaign') }}
                        </div>
                    </a>
                </li>
            </ul>

            <h1 class="mb-2 text--light ms--1"> {{ translate('AI CHAT BOT') }}</h1>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.ai_bots.index']) }}"
                        href="{{ route('user.ai_bots.index') }}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-robot text--light me-2"></i></span>
                            {{ translate('OpenAI Configuration') }}
                        </div>
                    </a>
                </li>


                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.ai_bots.custom_replies.*']) }}"
                        href="{{ route('user.ai_bots.custom_replies.index') }}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-reply-all text--light me-2"></i></span>
                            {{ translate('Custom Auto Reply') }}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.facebook.messenger.index']) }}"
                        href="{{ route('user.facebook.messenger.index') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 lab la-facebook-messenger text--light me-2"></i></span>
                            {{ translate('Facebook Messenger Bot') }}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.whatsapp.bot.index']) }}"
                        href="{{ route('user.whatsapp.bot.index') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 lab la-facebook-messenger text--light me-2"></i></span>
                            {{ translate('Whatsapp Bot') }}
                        </div>
                    </a>
                </li>
                <li>
                    <a class="ms--1 d--flex align--center {{ sidebarMenuActive(['user.ai_bots.share_link', 'user.advanced.index']) }} side_bar_nine_list"
                        href="javascript:void(0)">
                        <div><span class="me-4"><i
                                    class="fs-5 las la-link text--light me-2"></i></span>{{ translate('web bot') }}
                        </div>
                        <i class="las la-angle-down icon8"></i>
                    </a>
                    <ul
                        class="first_nine_child {{ menuActive(['user.ai_bots.share_link', 'user.advanced.index'], 9) }}">
                        <li>
                            <a class="{{ menuActive(['user.ai_bots.share_link', 'user.advanced.index']) }}"
                                href="{{ route('user.ai_bots.share_link') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('web bot') }}</a>
                            <a class="{{ menuActive('user.advanced.index') }}"
                                href="{{ route('user.advanced.index') }}"><i
                                    class="lab la-jira me-3"></i>{{ translate('Advanced web bot setting') }}</a>
                        </li>
                    </ul>
                </li>

            </ul>

            <h1 class="text--light ms--1 mb-2"> {{ translate('Messaging Gateways') }}</h1>
            <ul>
                <!-- SMS Settings -->
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.gateway.sms.index', 'user.gateway.sms.edit']) }}"
                        href="{{ route('user.gateway.sms.index') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 las la-sms text--light me-2"></i></span>{{ translate('SMS Gateway') }}
                        </div>
                    </a>
                </li>

            </ul>

            @if (auth()->user()->default_whatsapp_gateway == \App\Models\WhatsappLog::GATEWAY_DESKTOP)
                <ul>
                    <!-- Email Configuration -->
                    <li class="side_bar_list d--flex align--center">
                        <a class="ms--1 d--flex align--center {{ menuActive(['user.desktop.gateway.whatsapp.create']) }}"
                            href="{{ route('user.desktop.gateway.whatsapp.create') }}">
                            <div>
                                <span class="me-3"><i
                                        class="fs-5 fab fa-whatsapp text--light me-2"></i></span>{{ translate('Whatsapp Gateway') }}
                            </div>
                        </a>
                    </li>
                </ul>
            @elseif(
                !auth()->user()->default_whatsapp_gateway ||
                    auth()->user()->default_whatsapp_gateway == \App\Models\WhatsappLog::GATEWAY_WEB)
                <ul>
                    <!-- Email Configuration -->
                    <li class="side_bar_list d--flex align--center">
                        <a class="ms--1 d--flex align--center {{ menuActive(['user.gateway.whatsapp.create']) }}"
                            href="{{ route('user.gateway.whatsapp.create') }}">
                            <div>
                                <span class="me-3"><i
                                        class="fs-5 fab fa-whatsapp text--light me-2"></i></span>{{ translate('Whatsapp Gateway') }}
                            </div>
                        </a>
                    </li>
                </ul>
            @endif

            @if (in_array(auth()->user()->default_whatsapp_gateway, [
                    \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN,
                    \App\Models\WhatsappLog::GATEWAY_BUSINESS,
                ]))
                <ul>
                    <li>
                        <a class="ms--1 d--flex align--center {{ sidebarMenuActive([
                            'user.gateway.whatsapp.edit',
                            'user.gateway.whatsapp.create',
                            'user.business.whatsapp.account.create',
                            'user.business.whatsapp.template.index',
                            'user.desktop.whatsapp.messages.delete.index',
                        ]) }} side_bar_twenty_two_list"
                            href="javascript:void(0)">
                            <div><span class="me-4"><i class="fs-5 fab fa-whatsapp text--light"></i></span>
                                {{ translate('Whatsapp Gateway') }}</div>
                            <i class="las la-angle-down icon22"></i>
                        </a>
                        <ul
                            class="first_twenty_two_child {{ menuActive(
                                [
                                    'user.gateway.whatsapp*',
                                    'user.business.whatsapp.account.create',
                                    'user.business.whatsapp.template.index',
                                    'user.desktop.gateway.whatsapp.create',
                                    'user.whatsapp.messages.delete.index',
                                ],
                                22,
                            ) }}">
                            @if (auth()->user()->default_whatsapp_gateway == \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN)
                                <li>
                                    <a class="{{ menuActive(['user.business.whatsapp.account.create']) }}"
                                        href="{{ route('user.business.whatsapp.account.create') }}">
                                        <div>
                                            <span class="me-3"><i
                                                    class="lab la-jira me-3"></i></span>{{ translate('Whatsapp Gateway') }}
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ menuActive(['user.business.whatsapp.template.index']) }}"
                                        href="{{ route('user.business.whatsapp.template.index') }}">
                                        <div>
                                            <span class="me-3"><i
                                                    class="lab la-jira me-3"></i></span>{{ translate('Business Templates') }}
                                        </div>
                                    </a>
                                </li>
                            @elseif(auth()->user()->default_whatsapp_gateway == \App\Models\WhatsappLog::GATEWAY_BUSINESS)
                                <li>
                                    <a class="{{ menuActive(['user.business.whatsapp.account.create']) }}"
                                        href="{{ route('user.business.whatsapp.account.create') }}">
                                        <div>
                                            <span class="me-3"><i
                                                    class="lab la-jira me-3"></i></span>{{ translate('Whatsapp Gateway') }}
                                        </div>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                </ul>
            @endif

            <ul>
                <!-- Email Configuration -->
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.mail.configuration', 'user.mail.edit']) }}"
                        href="{{ route('user.mail.configuration') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 las la-envelope text--light me-2"></i></span>{{ translate('Email Gateway') }}
                        </div>
                    </a>
                </li>
            </ul>
            {{-- <ul>
                <a class="ms--1 d--flex align--center {{menuActive(['user.general.setting.index'])}}"
                    href="{{route('user.general.setting.index')}}">
                    <div>
                        <span class="me-3"><i
                                class="fs-5 las la-envelope text--light me-2"></i></span>{{ translate('Setting')}}
                    </div>
                </a>
            </ul> --}}


            <h1 class="text--light m-2">{{ translate('Support & Packages') }}</h1>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive('user.credits.create') }}"
                        href="{{ route('user.credits.create') }}">
                        <div>
                            <span class="me-3"><i
                                    class="fs-5 lab la-telegram-plane text--light me-2"></i></span>{{ translate('Buy Credits') }}
                        </div>
                    </a>
                </li>
            </ul>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{ menuActive(['user.ticket.index', 'user.ticket.detail', 'user.ticket.create']) }}"
                        href="{{ route('user.ticket.index') }}">
                        <div>
                            <span class="me-3"><i
                                    class="las la-ticket-alt fs-5 text--light me-2"></i></span>{{ translate('Support Ticket') }}
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        {{-- <br>
        <div class="text-center p-1 text-uppercase version">
            <span class="text--primary">{{ translate('iGENSOLUTIONSLTD')}}</span>
            <span class="text--success">{{ translate(config('requirements.core.appVersion'))}}</span>
        </div> --}}
    </div>
</div>
