<x-layouts.vault title="Кабинет автора — Message Vault">
    <section class="auth-panel" aria-labelledby="dashboard-title">
        <p class="eyebrow">Личный кабинет</p>
        <h1 id="dashboard-title">Капсулы</h1>

        @if (! auth()->user()->hasVerifiedEmail())
            <p>Подтвердите почту, чтобы публиковать капсулы.</p>
            <p><a class="underline" href="{{ route('verification.notice') }}">Подтвердить почту</a></p>
        @else
            <p>Здесь появятся ваши черновики и опубликованные капсулы.</p>
        @endif
    </section>
</x-layouts.vault>
