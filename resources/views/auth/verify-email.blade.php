<x-layouts.vault title="Подтверждение почты — Message Vault">
    <section class="auth-panel" aria-labelledby="verification-title">
        @if (auth()->user()->hasVerifiedEmail())
            <h1 id="verification-title">Почта подтверждена</h1>
            <p><a class="underline" href="{{ route('home') }}">На главную</a></p>
        @else
            <h1 id="verification-title">Подтвердите почту</h1>
            <p>Откройте ссылку из письма, отправленного на {{ auth()->user()->email }}.</p>

            @if (session('status'))
                <p role="status">{{ session('status') }}</p>
            @endif

            <form class="auth-form" method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="action" type="submit">Отправить письмо ещё раз</button>
            </form>
        @endif
    </section>
</x-layouts.vault>
