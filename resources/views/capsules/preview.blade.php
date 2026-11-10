<x-layouts.vault title="Предпросмотр капсулы — Message Vault">
    <section class="auth-panel capsule-editor capsule-preview" aria-labelledby="preview-title">
        <p class="eyebrow">Приватный предпросмотр</p>
        <h1 id="preview-title">{{ $capsule->title }}</h1>
        <p class="auth-hint">Последняя сохранённая версия черновика. Версия обработки {{ $preview->version }}.</p>
        <nav class="preview-presets" aria-label="Режим обработки">
            @foreach (\App\Capsules\TransformationPreset::cases() as $option)
                <a class="underline" href="{{ route('capsules.preview', ['capsule' => $capsule, 'preset' => $option->value]) }}"
                    @if ($preset === $option) aria-current="page" @endif>{{ $option->label() }}</a>
            @endforeach
        </nav>
        <p class="auth-hint">
            {{ $preset === \App\Capsules\TransformationPreset::Gentle
                ? 'Похожие буквы и невидимые пробелы меняют последовательность символов.'
                : 'Короткие группы слов дополнительно меняют порядок символов в HTML.' }}
        </p>

        <article class="capsule-preview__sheet" aria-label="Обработанный текст">
            <x-capsules.segments :segments="$preview->segments" />
        </article>

        <details class="extraction-report">
            <summary>Проверка извлечения текста</summary>
            <p class="auth-hint">Оба текста проходят одинаковую нормализацию. «Совпало» означает равенство после выбранной обработки.</p>
            <dl>
                @foreach ($extraction as $method => $matches)
                    <div>
                        <dt>{{ \App\Capsules\ExtractionReport::LABELS[$method] }}</dt>
                        <dd>{{ $matches ? 'Совпало' : 'Различается' }}</dd>
                    </div>
                @endforeach
            </dl>
        </details>

        <section class="publication-confirmation" aria-labelledby="publication-title">
            <h2 id="publication-title">Публикация</h2>
            <x-form-errors />
            @if (! auth()->user()->hasVerifiedEmail())
                <p>Для публикации <a class="underline" href="{{ route('verification.notice') }}">подтвердите почту</a>.</p>
            @elseif (! $capsule->opens_at?->isFuture())
                <p>Выберите будущую дату открытия в редакторе.</p>
            @else
                <p>Открытие {{ $capsule->opens_at->setTimezone(auth()->user()->timezone)->format('d.m.Y H:i') }} ({{ auth()->user()->timezone }}).</p>
                <p>Режим обработки — {{ $preset->label() }}.</p>
                <form class="auth-form" method="post" action="{{ route('capsules.publish', $capsule) }}">
                    @csrf
                    <input type="hidden" name="version" value="{{ $capsule->lock_version }}">
                    <input type="hidden" name="preset" value="{{ $preset->value }}">
                    <label class="attestation">
                        <input type="checkbox" name="author_attested" value="1" required @checked(old('author_attested'))>
                        <span>Я заявляю, что этот текст написан человеком.</span>
                    </label>
                    <p class="auth-hint">Это заявление автора. После публикации текст и дата открытия фиксируются.</p>
                    <button class="action" type="submit">Запечатать и опубликовать</button>
                </form>
            @endif
        </section>

        <p><a class="underline" href="{{ route('capsules.edit', $capsule) }}">Вернуться к редактору</a></p>
    </section>
</x-layouts.vault>
