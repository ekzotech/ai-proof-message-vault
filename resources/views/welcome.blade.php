<x-layouts.vault title="Капсула времени для текста — Message Vault">
    <div class="landing-lead">
        <section class="landing-intro" aria-labelledby="landing-title">
            <p class="eyebrow">Письма с датой открытия</p>
            <h1 id="landing-title">Капсула времени для текста</h1>
            <p class="landing-summary">
                Оставьте письмо, воспоминание или манифест для будущего. Ссылка откроет текст в назначенный день.
            </p>
            <a class="action" href="#capsule-example">Посмотреть пример</a>
        </section>

        <livewire:capsule-example />
    </div>

    <section class="landing-guide" aria-labelledby="guide-title">
        <h2 id="guide-title">Как устроена капсула</h2>

        <ol class="landing-steps" role="list">
            <li>
                <h3>01 / Написать</h3>
                <p>Подготовьте текст и выберите дату и время открытия.</p>
            </li>
            <li>
                <h3>02 / Запечатать</h3>
                <p>Поделитесь ссылкой. До выбранной даты читатель увидит только экран ожидания.</p>
            </li>
            <li>
                <h3>03 / Прочитать</h3>
                <p>После открытия текст можно прочитать на странице. Обработка может менять результат автоматического извлечения. Восстановление текста остаётся возможным.</p>
            </li>
        </ol>
    </section>
</x-layouts.vault>
