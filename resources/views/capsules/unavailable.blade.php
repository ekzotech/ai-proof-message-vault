<x-layouts.vault title="Капсула недоступна — Message Vault" :reader="true">
    <section class="auth-panel capsule-preview capsule-unavailable" aria-labelledby="unavailable-title">
        <p class="eyebrow">Читательская ссылка</p>
        <h1 id="unavailable-title">Капсула недоступна</h1>
        <p>Уточните ссылку у отправителя.</p>
        <p><a class="underline" href="{{ route('home') }}">На главную</a></p>
    </section>
</x-layouts.vault>
