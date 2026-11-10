<x-layouts.vault title="Публикация капсулы — Message Vault">
    <section class="auth-panel capsule-editor capsule-preview" aria-labelledby="publication-title">
        <p class="eyebrow">{{ $state->label() }}</p>
        <h1 id="publication-title">{{ $publication->title }}</h1>
        @if ($readerUrl !== null)
            <div class="auth-form" x-data="{ message: '' }">
                <div class="auth-field">
                    <label for="reader-link">Ссылка для читателя</label>
                    <input id="reader-link" type="url" value="{{ $readerUrl }}" readonly x-ref="link">
                </div>
                <button class="action" type="button" x-on:click="(async () => {
                    try {
                        await navigator.clipboard.writeText($refs.link.value);
                        message = 'Ссылка скопирована.';
                    } catch {
                        $refs.link.select();
                        message = 'Скопируйте выделенную ссылку вручную.';
                    }
                })()">Копировать ссылку</button>
                <p role="status" x-text="message"></p>
            </div>
            <div x-data>
                <button class="action" type="button" x-ref="revokeTrigger"
                    x-on:click="$refs.revokeDialog.showModal()">Отозвать ссылку</button>
                <dialog class="vault-dialog" x-ref="revokeDialog" aria-labelledby="revoke-title"
                    x-on:close="$refs.revokeTrigger.focus()">
                    <h2 id="revoke-title">Отозвать читательскую ссылку?</h2>
                    <p>Читатели потеряют доступ к капсуле по этой ссылке.</p>
                    <form class="dialog-actions" method="post" action="{{ route('capsules.revoke', $capsule) }}">
                        @csrf
                        @method('PATCH')
                        <button type="button" class="landing-secondary" autofocus
                            x-on:click="$refs.revokeDialog.close()">Отмена</button>
                        <button class="action" type="submit">Подтвердить отзыв</button>
                    </form>
                </dialog>
            </div>
        @endif
        <p><a class="underline" href="{{ route('capsules.delete', $capsule) }}">Удалить капсулу</a></p>
        <p><a class="underline" href="{{ route('dashboard') }}">В кабинет</a></p>
    </section>
</x-layouts.vault>
