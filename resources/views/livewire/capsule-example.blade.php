<section class="capsule-example" id="capsule-example" aria-labelledby="capsule-example-title">
    <p class="eyebrow">Демонстрационная капсула</p>
    <h2 id="capsule-example-title">Записка на потом</h2>

    <div class="capsule-example__status" aria-live="polite">
        @if ($opened)
            <p class="status-label">Открыта</p>
            <blockquote>В последний день осени мы посадили яблоню. Надеюсь, теперь она даёт тень всему двору.</blockquote>
        @else
            <p class="status-label">Запечатана</p>
            <p>Откройте пример, чтобы увидеть, как выглядит капсула после срока ожидания.</p>
        @endif
    </div>

    <button class="action" type="button" wire:click="toggle" wire:loading.attr="disabled">
        {{ $opened ? 'Закрыть пример' : 'Открыть пример' }}
    </button>
</section>
