<x-layouts.vault title="Кабинет автора — Message Vault">
    <section class="auth-panel capsule-editor" aria-labelledby="dashboard-title">
        <p class="eyebrow">Личный кабинет</p>
        <h1 id="dashboard-title">Капсулы</h1>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        <p><a class="underline" href="{{ route('profile.edit') }}">Настройки профиля</a></p>
        <p><a class="action" href="{{ route('capsules.create') }}">Создать черновик</a></p>

        @if (! auth()->user()->hasVerifiedEmail())
            <p>Подтвердите почту, чтобы публиковать капсулы.</p>
            <p><a class="underline" href="{{ route('verification.notice') }}">Подтвердить почту</a></p>
        @endif

        <div class="draft-list">
            <h2>Черновики</h2>
            @if ($drafts->isEmpty())
                <p>Здесь появятся ваши черновики.</p>
            @else
                <ul role="list">
                    @foreach ($drafts as $draft)
                        <li>
                            <a class="underline" href="{{ route('capsules.edit', $draft) }}">{{ $draft->title }}</a>
                            @if ($draft->opens_at)
                                <time datetime="{{ $draft->opens_at->toIso8601String() }}">
                                    {{ $draft->opens_at->setTimezone(auth()->user()->timezone)->format('d.m.Y H:i T') }}
                                </time>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{ $drafts->links() }}
            @endif
        </div>
        <div class="draft-list">
            <h2>Опубликованные капсулы</h2>
            @if ($published->isEmpty())
                <p>Здесь появятся опубликованные капсулы.</p>
            @else
                <ul role="list">
                    @foreach ($published as $capsule)
                        <li>
                            <a class="underline" href="{{ route('capsules.publication', $capsule) }}">{{ $capsule->title }}</a>
                            <p class="auth-hint">{{ \App\Capsules\CapsuleState::at($capsule->publication, now())->label() }}</p>
                        </li>
                    @endforeach
                </ul>
                {{ $published->links() }}
            @endif
        </div>
    </section>
</x-layouts.vault>
