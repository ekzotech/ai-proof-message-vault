<x-layouts.vault :title="$publication->title . ' — Message Vault'" :reader="true">
    <section class="auth-panel capsule-editor capsule-preview" aria-labelledby="reader-title">
        <p class="eyebrow">Капсула открыта</p>
        <h1 id="reader-title">{{ $publication->title }}</h1>
        @if ($publication->author_attested)
            <p class="auth-hint">Автор заявляет, что послание написано человеком.</p>
        @endif
        <p><a class="underline" href="{{ $viewUrl }}">{{ $plain ? 'Обработанный текст' : 'Обычный текст для скринридера' }}</a></p>
        @if ($plain)
            <p class="auth-hint">Этот режим сохраняет порядок чтения и облегчает автоматическое извлечение текста.</p>
        @endif
        <article class="capsule-preview__sheet" aria-label="Послание">
            @if ($plain)
                <div class="capsule-text" dir="auto">{{ $publication->accessible_text }}</div>
            @else
                <x-capsules.segments :segments="$publication->segments" />
            @endif
        </article>
    </section>
</x-layouts.vault>
