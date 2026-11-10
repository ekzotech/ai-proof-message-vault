<x-layouts.vault title="Предпросмотр капсулы — Message Vault">
    <section class="auth-panel capsule-editor capsule-preview" aria-labelledby="preview-title">
        <p class="eyebrow">Приватный предпросмотр</p>
        <h1 id="preview-title">{{ $capsule->title }}</h1>
        <p class="auth-hint">Последняя сохранённая версия черновика. Версия обработки {{ $preview->version }}.</p>

        <article class="capsule-preview__sheet" aria-label="Обработанный текст">
            <x-capsules.segments :segments="$preview->segments" />
        </article>

        <p><a class="underline" href="{{ route('capsules.edit', $capsule) }}">Вернуться к редактору</a></p>
    </section>
</x-layouts.vault>
