<x-layouts.vault title="Регистрация — Message Vault">
    <section class="auth-panel" aria-labelledby="register-title">
        <h1 id="register-title">Создать аккаунт</h1>

        <x-form-errors />

        <form class="auth-form" method="post" action="{{ route('register') }}">
            @csrf

            <div class="auth-field">
                <label for="name">Имя</label>
                @php($name = old('name'))
                <input id="name" name="name" value="{{ is_string($name) ? $name : '' }}" autocomplete="name" maxlength="100" required
                    @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name')
                    <p class="auth-error" id="name-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="email">Электронная почта</label>
                @php($address = old('email'))
                <input id="email" name="email" type="email" value="{{ is_string($address) ? $address : '' }}" autocomplete="email" maxlength="254" required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p class="auth-error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required
                    aria-describedby="password-hint @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror>
                <p class="auth-hint" id="password-hint">Минимум 12 символов.</p>
                @error('password')
                    <p class="auth-error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password_confirmation">Повторите пароль</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required>
            </div>

            <button class="action" type="submit">Зарегистрироваться</button>
        </form>
    </section>
</x-layouts.vault>
