@props(['heading', 'code'])
<x-layouts.vault :title="$heading . ' — Message Vault'" :reader="true">
    <section class="auth-panel capsule-preview" aria-labelledby="error-title">
        <p class="eyebrow">{{ $code }}</p>
        <h1 id="error-title">{{ $heading }}</h1>
        <p>{{ $slot }}</p>
        <p><a class="underline" href="{{ route('home') }}">На главную</a></p>
    </section>
</x-layouts.vault>
