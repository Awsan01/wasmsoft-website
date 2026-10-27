{{--
    Self-hosted web fonts (preload links + @font-face rules from the Vite
    font pipeline). Use instead of the @fonts directive: the inline style
    block is registered with the CSP by hash, after dropping the duplicate
    WOFF rules (App\Support\FontFaceRules). `aliases` limits the fonts
    loaded (null loads every configured font).
--}}
@props(['aliases' => null])

@php
    $fontsHtml = preg_replace_callback('#(<style[^>]*>)(.*?)(</style>)#s', function (array $style): string {
        $css = \App\Support\FontFaceRules::withoutWoffTwins($style[2]);
        app(\App\Security\InlineStyles::class)->allow($css);

        return $style[1].$css.$style[3];
    }, (string) \Illuminate\Support\Facades\Vite::fonts($aliases));
@endphp

{!! $fontsHtml !!}
