<x-layouts.vault title="Удаление капсулы — Message Vault">
    <section class="auth-panel capsule-preview" aria-labelledby="deletion-title">
        <h1 id="deletion-title">Удалить капсулу?</h1>
        <p>«{{ $capsule->title }}» и её опубликованная копия будут удалены. Ссылка перестанет открываться.</p>
        <form class="auth-form" method="post" action="{{ route('capsules.destroy', $capsule) }}">
            @csrf
            @method('DELETE')
            <button class="action" type="submit">Удалить капсулу</button>
            <a class="underline" href="{{ route('dashboard') }}">Отмена</a>
        </form>
    </section>
</x-layouts.vault>
