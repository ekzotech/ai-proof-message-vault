<x-layouts.vault title="Восстановление пароля — Message Vault">
    <section class="auth-panel" aria-labelledby="forgot-password-title">
        <h1 id="forgot-password-title">Восстановление пароля</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <x-form-errors />

        <form class="auth-form" method="post" action="{{ route('password.email') }}">
            @csrf
            <div class="auth-field">
                <label for="email">Электронная почта</label>
                @php($address = old('email'))
                <input id="email" name="email" type="email" value="{{ is_string($address) ? $address : '' }}" autocomplete="email" maxlength="254" required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p class="auth-error" id="email-error">{{ $message }}</p>
                @enderror
            </div>
            <button class="action" type="submit">Отправить ссылку</button>
        </form>
    </section>
</x-layouts.vault>
