{{--
    Self-hosted web fonts (preload links + @font-face rules from the Vite
    font pipeline). Use instead of the @fonts directive: the inline style
    block is registered with the CSP by hash. `aliases` limits the fonts
    loaded (null loads every configured font).
--}}
@props(['aliases' => null])

@php
    $fontsHtml = (string) \Illuminate\Support\Facades\Vite::fonts($aliases);

    if (preg_match_all('#<style[^>]*>(.*?)</style>#s', $fontsHtml, $inlineStyles) > 0) {
        foreach ($inlineStyles[1] as $css) {
            app(\App\Security\InlineStyles::class)->allow($css);
        }
    }
@endphp

{!! $fontsHtml !!}
