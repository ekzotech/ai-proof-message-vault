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

        <p><a class="underline" href="{{ route('capsules.edit', $capsule) }}">Вернуться к редактору</a></p>
    </section>
</x-layouts.vault>
