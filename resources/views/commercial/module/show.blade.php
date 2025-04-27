<div class="flex-h1">
    <h1>Детали брифа

    </h1>
    <button onclick="window.open('{{ route('commercial.download.pdf', $brif->id) }}')" class="btn btn-primary" style=" ">
            
        Скачать PDF
    </button>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Поле</th>
            <th>Значение</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ID</td>
            <td>{{ $brif->id }}</td>
        </tr>
        <tr>
            <td>Название</td>
            <td>{{ $brif->title }}</td>
        </tr>
        <tr>
            <td>Описание</td>
            <td>{{ $brif->description }}</td>
        </tr>
        <tr>
            <td>Статус</td>
            <td>{{ $brif->status }}</td>
        </tr>
        <tr>
            <td>User ID</td>
            <td>{{ $brif->user_id }}</td>
        </tr>
        <tr>
            <td>Дата создания</td>
            <td>{{ $brif->created_at }}</td>
        </tr>
        <tr>
            <td>Дата обновления</td>
            <td>{{ $brif->updated_at }}</td>
        </tr>
    </tbody>
</table>

<!-- Отображаем общий бюджет -->
<h2><strong>Общий бюджет:</strong> {{ number_format($brif->price, 2, ',', ' ') }} ₽</h2>

<!-- Если бюджет разбивается по зонам -->
@if($zones)
    <h3>Бюджет по зонам</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Зона</th>
                <th>Бюджет</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zones as $index => $zone)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                    <td>
                        @if (isset($price[$index]))
                            {{ number_format($price[$index], 2, ',', ' ') }} ₽ <!-- Форматируем цену для каждой зоны -->
                        @else
                            0 ₽
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<h2>Зоны</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Название</th>
            <th>Описание</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($zones as $index => $zone)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                <td>{{ $zone['description'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@foreach ($preferencesFormatted as $zoneName => $zonePreferences)
    <h3>Предпочтения для {{ $zoneName }}</h3> <!-- Здесь выводится название зоны -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Вопрос</th>
                <th>Ответ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zonePreferences as $index => $preference)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $preference['question'] }}</td>
                    <td>{{ $preference['answer'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>@include('layouts/mobponel')
@endforeach
