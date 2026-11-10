<x-layouts.vault title="Вход — Message Vault">
    <section class="auth-panel" aria-labelledby="login-title">
        <h1 id="login-title">Войти в аккаунт</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <x-form-errors />

        <form class="auth-form" method="post" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <label for="email">Электронная почта</label>
                @php($address = old('email'))
                <input id="email" name="email" type="email" value="{{ is_string($address) ? $address : '' }}" autocomplete="username" maxlength="254" required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p class="auth-error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')
                    <p class="auth-error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="action" type="submit">Войти</button>
            <a class="underline" href="{{ route('password.request') }}">Забыли пароль?</a>
        </form>
    </section>
</x-layouts.vault>
