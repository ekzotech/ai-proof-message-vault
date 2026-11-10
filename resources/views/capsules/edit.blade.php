<x-layouts.vault title="Черновик — Message Vault">
    <section class="auth-panel capsule-editor" aria-labelledby="draft-title">
        <p class="eyebrow">Черновик</p>
        <h1 id="draft-title">Редактировать послание</h1>
        <p><a class="underline" href="{{ route('capsules.preview', $capsule) }}">Предпросмотр сохранённого текста</a></p>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        <livewire:capsule-draft-editor :capsule="$capsule" />
        <p class="mt-6"><a class="underline" href="{{ route('capsules.delete', $capsule) }}">Удалить черновик</a></p>
    </section>
</x-layouts.vault>
