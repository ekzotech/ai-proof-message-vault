<x-layouts.vault title="Профиль — Message Vault">
    <section class="auth-panel" aria-labelledby="profile-title">
        <h1 id="profile-title">Профиль</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <x-form-errors />

        <form class="auth-form" method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')
            <div class="auth-field">
                <label for="timezone">Часовой пояс</label>
                <select id="timezone" name="timezone" aria-describedby="timezone-hint @error('timezone') timezone-error @enderror"
                    @error('timezone') aria-invalid="true" @enderror>
                    @foreach (DateTimeZone::listIdentifiers() as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', auth()->user()->timezone) === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
                <p class="auth-hint" id="timezone-hint">Даты открытия в редакторе вводятся в этом часовом поясе.</p>
                @error('timezone')
                    <p class="auth-error" id="timezone-error">{{ $message }}</p>
                @enderror
            </div>
            <button class="action" type="submit">Сохранить</button>
        </form>
    </section>
</x-layouts.vault>
