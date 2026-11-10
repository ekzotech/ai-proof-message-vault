@if ($errors->any())
    <div class="form-errors" tabindex="-1" autofocus aria-labelledby="form-errors-title">
        <p id="form-errors-title">Проверьте данные формы</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
