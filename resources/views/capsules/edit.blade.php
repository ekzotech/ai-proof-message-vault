<x-layouts.vault title="Черновик — Message Vault">
    <section class="auth-panel" aria-labelledby="draft-title">
        <p class="eyebrow">Черновик</p>
        <h1 id="draft-title">{{ $capsule->title }}</h1>
        <p class="whitespace-pre-wrap break-words">{{ $capsule->body }}</p>
    </section>
</x-layouts.vault>
