<x-layouts.vault title="Новый пароль — Message Vault">
    <section class="auth-panel" aria-labelledby="reset-password-title">
        <h1 id="reset-password-title">Новый пароль</h1>
        <form class="auth-form" method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="auth-field">
                <label for="email">Электронная почта</label>
                @php($address = old('email', request()->query('email')))
                <input id="email" name="email" type="email" value="{{ is_string($address) ? $address : '' }}" autocomplete="username" maxlength="254" required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p class="auth-error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password">Новый пароль</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')
                    <p class="auth-error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password_confirmation">Повторите пароль</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required>
            </div>

            <button class="action" type="submit">Сохранить пароль</button>
            <a class="underline" href="{{ route('password.request') }}">Запросить новую ссылку</a>
        </form>
    </section>
</x-layouts.vault>
