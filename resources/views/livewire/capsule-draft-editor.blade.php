<form class="auth-form" wire:submit="save">
    <div class="auth-field">
        <label for="capsule-title">Заголовок</label>
        <input id="capsule-title" wire:model="title" value="{{ $title }}" required
            @error('title') aria-invalid="true" aria-describedby="capsule-title-error" @enderror>
        @error('title')
            <p class="auth-error" id="capsule-title-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="auth-field">
        <label for="capsule-body">Текст послания</label>
        <textarea id="capsule-body" wire:model="body" rows="8" required
            @error('body') aria-invalid="true" aria-describedby="capsule-body-error" @enderror>{{ $body }}</textarea>
        @error('body')
            <p class="auth-error" id="capsule-body-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <button class="action" type="submit" wire:loading.attr="disabled">Сохранить черновик</button>
</form>
