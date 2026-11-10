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

        <nav class="preview-presets" aria-label="Фильтр капсул">
            <a class="underline" href="{{ route('dashboard') }}" @if ($state === null) aria-current="page" @endif>Все</a>
            @foreach (\App\Capsules\CapsuleState::cases() as $filter)
                <a class="underline" href="{{ route('dashboard', ['status' => $filter->value]) }}"
                    @if ($state === $filter) aria-current="page" @endif>{{ $filter->label() }}</a>
            @endforeach
        </nav>

        @if ($drafts !== null)
        <div class="draft-list">
            <h2>Черновики</h2>
            @if ($drafts->isEmpty())
                <p>Здесь появятся ваши черновики.</p>
            @else
                <ul role="list">
                    @foreach ($drafts as $draft)
                        <li>
                            <a class="underline" href="{{ route('capsules.edit', $draft) }}">{{ $draft->title }}</a>
                            <div class="capsule-list-meta">
                                <span class="status-label">Черновик</span>
                                @if ($draft->opens_at)
                                    <time datetime="{{ $draft->opens_at->toIso8601String() }}">
                                        {{ $draft->opens_at->setTimezone(auth()->user()->timezone)->format('d.m.Y H:i T') }}
                                    </time>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                {{ $drafts->links() }}
            @endif
        </div>
        @endif
        @if ($published !== null)
        <div class="draft-list">
            <h2>Опубликованные капсулы</h2>
            @if ($published->isEmpty())
                <p>Здесь появятся опубликованные капсулы.</p>
            @else
                <ul role="list">
                    @foreach ($published as $capsule)
                        <li>
                            <a class="underline" href="{{ route('capsules.publication', $capsule) }}">{{ $capsule->title }}</a>
                            <div class="capsule-list-meta">
                                <span class="status-label">{{ \App\Capsules\CapsuleState::at($capsule->publication, $now)->label() }}</span>
                                <time datetime="{{ $capsule->publication->opens_at->toIso8601String() }}">
                                    {{ $capsule->publication->opens_at->setTimezone(auth()->user()->timezone)->format('d.m.Y H:i T') }}
                                </time>
                            </div>
                        </li>
                    @endforeach
                </ul>
                {{ $published->links() }}
            @endif
        </div>
        @endif
    </section>
</x-layouts.vault>
