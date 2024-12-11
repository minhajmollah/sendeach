@props(['id' => 'example', 'title' => 'Copy', 'language' => 'bat', 'theme' => 'material-theme-lighter'])

<div class="code-box-copy" {{ $attributes }}>
    <button class="code-box-copy__btn" data-clipboard-target="#{{ $id }}" title="Copy"></button>
    <pre><code id="{{ $id }}">{{ $slot }}</code></pre>
</div>


@pushonce('stylepush')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">

    <link href="{{ asset('assets/syntax-highlighter/code-box-copy/css/code-box-copy.css') }}" rel="stylesheet">
@endpushonce

@pushonce('scriptpush')
    <script src="{{ asset('assets/syntax-highlighter/clipboard/clipboard.min.js') }}" rel="stylesheet"></script>
    <script src="{{ asset('assets/syntax-highlighter/code-box-copy/js/code-box-copy.js') }}" rel="stylesheet"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();
        $('.code-box-copy').codeBoxCopy();
    </script>
@endpushonce
