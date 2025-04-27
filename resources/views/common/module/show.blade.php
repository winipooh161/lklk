<div class="flex-h1">
    <h1>Детали брифа

    </h1>
    <button onclick="window.open('{{ route('commercial.download.pdf', $brif->id) }}')" class="btn btn-primary" style=" ">
            
        Скачать PDF
    </button>
</div>
<table class="table table-bordered" style="">
    <thead>
        <tr style="">
            <th style="">Поле</th>
            <th style="">ОТВЕТ</th>
        </tr>
    </thead>
    <tbody style="margin-bottom: 30px;">
        <tr>
            <td style="">Артикль</td>
            <td style="">{{ $brif->article }}</td>
        </tr>
        <tr>
            <td style="">Название</td>
            <td style="">{{ $brif->title }}</td>
        </tr>
        <tr>
            <td style="">Общая сумма</td>
            <td style="">{{ $brif->price }} руб</td>
        </tr>
        <tr>
            <td style="">Описание</td>
            <td style="">{{ $brif->description }}</td>
        </tr>
        <tr>
            <td style="">Статус</td>
            <td style="">{{ $brif->status }}</td>
        </tr>
        <tr>
            <td style="">Создатель брифа</td>
            <td style="">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="">Номер клиента</td>
            <td style="">{{ $user->phone }}</td>
        </tr>
    </tbody>
    <tbody>
        {{-- Выбранные комнаты --}}
        <tr class="hd-show">
            <td colspan="2" style="">
                <h3 style="font-size: 16px; margin: 0; text-align:left;">Выбранные помещения</h3>
            </td>
        </tr>
        <tr>
            <td style="">Помещения</td>
            <td style="">
                @if($brif->rooms && count(json_decode($brif->rooms, true)) > 0)
                    @foreach(json_decode($brif->rooms, true) as $room_key => $room_title)
                        {{ $room_title }}@if(!$loop->last), @endif
                    @endforeach
                @else
                    Не выбраны помещения
                @endif
            </td>
        </tr>

        {{-- Display questions and answers for 5 pages --}}
        @for ($i = 1; $i <= 5; $i++)
            <tr class="hd-show">
                <td colspan="2" style="">
                    <h3 style="font-size: 16px; margin: 0; text-align:left;">{{ $pageTitlesCommon[$i - 1] }}</h3>
                </td>
            </tr>
            @if(isset($questions[$i]))
                @foreach ($questions[$i] as $question)
                    @php
                        $field = $question['key'];
                    @endphp
                    @if (isset($brif->$field) && !empty($brif->$field))
                        <tr>
                            <td style="">{{ $question['title'] }}</td>
                            <td style="">{{ $brif->$field }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
            
            {{-- Отображение референсов для страницы 2 --}}
            @if ($i == 2 && $brif->references)
                <tr>
                    <td style="">Референсы</td>
                    <td style="">
                        @if(is_array(json_decode($brif->references, true)) && count(json_decode($brif->references, true)) > 0)
                            @foreach(json_decode($brif->references, true) as $reference)
                                <div><a href="{{ asset($reference) }}" target="_blank">{{ basename($reference) }}</a></div>
                            @endforeach
                        @else
                            Референсы не загружены
                        @endif
                    </td>
                </tr>
            @endif
        @endfor
    </tbody>

  
    <tbody>
        <tr>
            <td style="">Дата создания</td>
            <td style="">{{ $brif->created_at }}</td>
        </tr>
        <tr>
            <td style="">Дата обновления</td>
            <td style="">{{ $brif->updated_at }}</td>
        </tr>
    </tbody>
</table>@include('layouts/mobponel')
