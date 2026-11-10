<x-layouts.vault :title="$publication->title . ' — Message Vault'" :reader="true">
    <section class="auth-panel capsule-editor capsule-preview" aria-labelledby="reader-title">
        <p class="eyebrow">Капсула открыта</p>
        <h1 id="reader-title">{{ $publication->title }}</h1>
        @if ($publication->author_attested)
            <p class="auth-hint">Автор заявляет, что послание написано человеком.</p>
        @endif
        <article class="capsule-preview__sheet" aria-label="Послание">
            <x-capsules.segments :segments="$publication->segments" />
        </article>
    </section>
</x-layouts.vault>
