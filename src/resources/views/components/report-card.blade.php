@props(['title' => '', 'value' => '', 'icon_class' => 'bg-secondary', 'text_class', 'link', 'link_text' => 'View'])

<div {{ $attributes->merge(['class'=> "row p-0 bg-white align-items-center rounded shadow text-dark m-0"]) }}>
    <div
        class="h-100 m-0 p-0 col-2 row justify-content-center align-items-center rounded border-2 text-white {{ $icon_class }}">
        {!! @$slot !!}
    </div>
    <div class="col-10 m-0 d-flex py-3 align-items-center  justify-content-between text-black {{ @$text_class }}">
        <div class="d-flex flex-column">
            <h6 class="report-title">{{ @$title }}</h6>
            <p class="report-value">{{ @$value }}</p>
        </div>
        @if(@$link)
            <a href="{{ $link }}" class="float-end link-primary link">{{ $link_text }}</a>
        @endif
    </div>
</div>
