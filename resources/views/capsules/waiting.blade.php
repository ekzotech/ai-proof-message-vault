<x-layouts.vault title="Капсула ожидает открытия — Message Vault" :reader="true">
    <section class="auth-panel capsule-editor capsule-preview" aria-labelledby="waiting-title">
        <p class="eyebrow">Запечатана</p>
        <h1 id="waiting-title">Капсула ожидает открытия</h1>
        <p>Послание станет доступно
            <time datetime="{{ $opensAt->toIso8601String() }}">{{ $opensAt->format('d.m.Y в H:i') }} UTC</time>.
        </p>
    </section>
</x-layouts.vault>
