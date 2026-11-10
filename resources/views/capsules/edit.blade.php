<x-layouts.vault title="Черновик — Message Vault">
    <section class="auth-panel capsule-editor" aria-labelledby="draft-title">
        <p class="eyebrow">Черновик</p>
        <h1 id="draft-title">Редактировать послание</h1>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        <livewire:capsule-draft-editor :capsule="$capsule" />
    </section>
</x-layouts.vault>
