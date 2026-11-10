<form class="auth-form" wire:submit="save">
    @error('conflict')
        <p class="auth-error" role="alert">{{ $message }}</p>
    @enderror

    <div class="auth-field">
        <label for="capsule-title">Заголовок</label>
        <input id="capsule-title" wire:model="title" value="{{ $title }}" required
            aria-describedby="capsule-title-count @error('title') capsule-title-error @enderror"
            @error('title') aria-invalid="true" @enderror>
        <p class="auth-hint" id="capsule-title-count">
            Символов <span x-text="Array.from($wire.title).length">{{ mb_strlen($title) }}</span> из {{ \App\Rules\CapsuleText::MAX_TITLE }}
        </p>
        @error('title')
            <p class="auth-error" id="capsule-title-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="auth-field">
        <label for="capsule-body">Текст послания</label>
        <textarea id="capsule-body" wire:model="body" rows="8" required
            aria-describedby="capsule-body-count @error('body') capsule-body-error @enderror"
            @error('body') aria-invalid="true" @enderror>{{ $body }}</textarea>
        <p class="auth-hint" id="capsule-body-count">
            Символов <span x-text="Array.from($wire.body).length">{{ mb_strlen($body) }}</span> из {{ \App\Rules\CapsuleText::MAX_BODY }}
        </p>
        @error('body')
            <p class="auth-error" id="capsule-body-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="auth-field">
        <label for="capsule-opening">Дата и время открытия</label>
        <input id="capsule-opening" type="datetime-local" wire:model.live.change="opening"
            aria-describedby="opening-zone @error('opening') opening-error @enderror"
            @error('opening') aria-invalid="true" @enderror>
        <p class="auth-hint" id="opening-zone">Часовой пояс {{ $timezone }}. Дату можно выбрать позже.</p>
        @error('opening')
            <p class="auth-error" id="opening-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @if (count($offsets) > 1)
        <div class="auth-field">
            <label for="opening-offset">Смещение UTC</label>
            <select id="opening-offset" wire:model="openingOffset"
                @error('openingOffset') aria-invalid="true" aria-describedby="opening-offset-error" @enderror>
                <option value="">Выберите смещение</option>
                @foreach ($offsets as $offset)
                    <option value="{{ $offset }}">UTC{{ $offset }}</option>
                @endforeach
            </select>
            @error('openingOffset')
                <p class="auth-error" id="opening-offset-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <button class="action" type="submit" wire:loading.attr="disabled">Сохранить черновик</button>
</form>
