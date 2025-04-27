@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="create-deal-form" action="{{ route('deals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- Добавляем скрытые поля для datestamps, которые заполняются скриптом -->
    <input type="hidden" name="start_date" id="start_date">
    <input type="hidden" name="project_duration" id="project_duration">
    <input type="hidden" name="project_end_date" id="project_end_date">

    <!-- БЛОК: Основная информация -->
    <fieldset class="module">
        <legend><h1>{{ $title_site }}</h1></legend>
       
        <div class="form-group-deal">
            <label for="price_service_option" title="Выберите услугу из доступного прайса"><i class="fas fa-list-check"></i> Услуга по прайсу: <span class="required">*</span></label>
            <select id="price_service_option" name="price_service_option" class="form-control" required title="Обязательное поле: выберите тип услуги по прайсу">
                <option value="">-- Выберите услугу --</option>
                <option value="экспресс планировка" title="Базовая планировка помещения без детализации">Экспресс планировка</option>
                <option value="экспресс планировка с коллажами" title="Планировка с добавлением коллажей для визуализации">Экспресс планировка с коллажами</option>
                <option value="экспресс проект с электрикой" title="Проект с планом электрических точек и размещения розеток">Экспресс проект с электрикой</option>
                <option value="экспресс планировка с электрикой и коллажами" title="Полный проект планировки с электросхемами и визуальными коллажами">Экспресс планировка с электрикой и коллажами</option>
                <option value="экспресс проект с электрикой и визуализацией" title="Проект с планом электрических точек и 3D визуализацией">Экспресс проект с электрикой и визуализацией</option>
                <option value="экспресс рабочий проект" title="Проект с рабочей документацией">Экспресс рабочий проект</option>
                <option value="экспресс эскизный проект с рабочей документацией" title="Концептуальный эскизный проект с необходимой рабочей документацией">Экспресс эскизный проект с рабочей документацией</option>
                <option value="экспресс 3Dвизуализация" title="Только 3D визуализация пространства без рабочей документации">Экспресс 3Dвизуализация</option>
                <option value="экспресс полный дизайн-проект" title="Комплексный дизайн-проект включающий все этапы проектирования">Экспресс полный дизайн-проект</option>
                <option value="360 градусов" title="Панорамная 360-градусная визуализация пространства">360 градусов</option>
            </select>
        </div>  
        <div class="form-group-deal">
            <label for="rooms_count_pricing" title="Укажите количество комнат для расчёта цены"><i class="fas fa-door-open"></i> Количество комнат по прайсу:</label>
            <input type="number" id="rooms_count_pricing" name="rooms_count_pricing" class="form-control" title="Введите число комнат для корректного расчета стоимости проекта">
        </div>
        <div class="form-group-deal">
            <label for="execution_order_comment" title="Дополнительная информация для отдела исполнения"><i class="fas fa-comment"></i> Комментарий к Заказу для отдела исполнения:</label>
            <textarea id="execution_order_comment" name="execution_order_comment" class="form-control" rows="3" maxlength="1000" title="Укажите особые требования или пожелания для отдела исполнения"></textarea>
        </div>
        <div class="form-group-deal">
            <label for="execution_order_file" title="Прикрепите файл с дополнительной информацией"><i class="fas fa-paperclip"></i> Прикрепить файл (PDF, JPG, PNG):</label>
            <input type="file" id="execution_order_file" name="execution_order_file" accept=".pdf,image/*" title="Допустимые форматы: PDF, JPG, PNG">
        </div>
        <div class="form-group-deal">
            <label for="package" title="Номер пакета услуг"><i class="fas fa-box"></i> Пакет (1, 2 или 3): <span class="required">*</span></label>
            <input type="text" id="package" name="package" class="form-control" required title="Обязательное поле: введите номер пакета (1, 2 или 3)">
        </div>
        <div class="form-group-deal">
            <label for="name" title="Укажите ФИО клиента"><i class="fas fa-user"></i> ФИО клиента: <span class="required">*</span></label>
            <input type="text" id="name" name="name" class="form-control" required title="Обязательное поле: введите ФИО клиента полностью">
        </div>
        <div class="form-group-deal">
            <label for="client_phone" title="Контактный номер телефона клиента"><i class="fas fa-phone"></i> Телефон: <span class="required">*</span></label>
            <input type="text" id="client_phone" name="client_phone"  class="form-control maskphone" required title="Обязательное поле: введите номер телефона клиента в формате +7 (XXX) XXX-XX-XX">
        </div>
        <div class="form-group-deal">
            <label for="client_timezone" title="Город проживания клиента и его часовой пояс"><i class="fas fa-city"></i> Город/часовой пояс:</label>
            <select id="client_timezone" name="client_timezone" class="form-control" title="Выберите город клиента для определения часового пояса">
                 <option value="">-- Выберите город --</option>
            </select>
        </div>
       
        <script>
            document.addEventListener("DOMContentLoaded", function(){
                // Автоматически устанавливаем сегодняшнюю дату в поле "Дата начала проекта"
                var today = new Date().toISOString().split("T")[0];
                document.getElementById("start_date").value = today;
                
                // При изменении срока проекта обновляем "Дата завершения проекта"
                document.getElementById("project_duration").addEventListener("input", function(){
                     var duration = parseInt(this.value, 10);
                     if (!isNaN(duration)) {
                         var startDate = new Date(document.getElementById("start_date").value);
                         startDate.setDate(startDate.getDate() + duration);
                         var endDate = startDate.toISOString().split("T")[0];
                         document.getElementById("project_end_date").value = endDate;
                     } else {
                         document.getElementById("project_end_date").value = "";
                     }
                });
            });
        </script>
        <!-- Убираем блок выбора партнёра, если пользователь partner -->
        @if(auth()->user()->status == 'partner')
            <div class="form-group-deal">
                <label title="Информация о партнере сделки"><i class="fas fa-handshake"></i> Партнер</label>
                <p title="Вы являетесь партнером в этой сделке">{{ auth()->user()->name }}</p>
                <input type="hidden" name="office_partner_id" value="{{ auth()->id() }}">
            </div>
        @else
            <!-- Если не partner, отображаем выбор партнеров -->
            <div class="form-group-deal">
                <label for="office_partner_id" title="Выберите партнера для сделки"><i class="fas fa-handshake"></i> Партнер:</label>
                <select id="office_partner_id" name="office_partner_id" class="form-control" title="Выберите партнера, который будет участвовать в сделке">
                    <option value="">-- Не выбрано --</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" title="{{ $partner->email ?? 'Email не указан' }}">{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Добавляем поле "Кто делает комплектацию" -->
        @if(in_array(auth()->user()->status, ['partner', 'coordinator', 'admin']))
            <div class="form-group-deal">
                <label for="completion_responsible" title="Укажите, кто отвечает за комплектацию проекта"><i class="fas fa-clipboard-check"></i> Кто делает комплектацию:<span class="required">*</span></label>
                <select id="completion_responsible" name="completion_responsible" class="form-control" required title="Обязательное поле: выберите ответственного за комплектацию">
                    <option value="">-- Выберите --</option>
                    <option value="клиент" title="Клиент самостоятельно выполняет комплектацию">Клиент</option>
                    <option value="партнер" title="Партнер отвечает за комплектацию">Партнер</option>
                    <option value="шопинг-лист" title="Предоставляется только список необходимых предметов">Шопинг-лист</option>
                    <option value="закупки и снабжение от УК" title="Управляющая компания берет на себя все закупки">Нужны закупки и снабжение от УК</option>
                </select>
            </div>
        @endif

        <!-- Убираем блок выбора координатора, если пользователь coordinator -->
        @if(auth()->user()->status == 'coordinator')
            <div class="form-group-deal">
                <label><i class="fas fa-user-tie"></i> Отв. координатор</label>
                <p>{{ auth()->user()->name }}</p>
                <input type="hidden" name="coordinator_id" value="{{ auth()->id() }}">
            </div>
        @else
           
        @endif
        <div class="form-group-deal">
            <label for="total_sum" title="Общая стоимость сделки"><i class="fas fa-ruble-sign"></i> Общая сумма:</label>
            <input type="number" step="0.01" id="total_sum" name="total_sum" class="form-control" title="Введите общую сумму сделки в рублях">
        </div>
        <div class="form-group-deal">
            <label for="measuring_cost" title="Стоимость услуг по замеру помещения"><i class="fas fa-ruler-combined"></i> Стоимость замеров:</label>
            <input type="number" step="0.01" id="measuring_cost" name="measuring_cost" class="form-control" title="Введите стоимость замеров помещения в рублях">
        </div>
        <div class="form-group-deal">
            <label for="client_info" title="Дополнительная информация о клиенте"><i class="fas fa-info-circle"></i> Информация о клиенте:</label>
            <textarea id="client_info" name="client_info" class="form-control" rows="3" title="Укажите полезную информацию о клиенте (предпочтения, особенности)"></textarea>
        </div>
        <div class="form-group-deal">
            <label for="payment_date" title="Дата поступления оплаты от клиента"><i class="fas fa-calendar-alt"></i> Дата оплаты:</label>
            <input type="date" id="payment_date" name="payment_date" class="form-control" title="Укажите дату, когда поступила или ожидается оплата">
        </div>
        <div class="form-group-deal">
            <label for="execution_comment" title="Комментарии по исполнению проекта"><i class="fas fa-comment-dots"></i> Комментарий (исполнение):</label>
            <textarea id="execution_comment" name="execution_comment" class="form-control" rows="3" maxlength="1000" title="Укажите особые требования или комментарии по исполнению проекта"></textarea>
        </div>
        <div class="form-group-deal">
            <label for="comment" title="Общий комментарий по сделке"><i class="fas fa-sticky-note"></i> Общий комментарий:</label>
            <textarea id="comment" name="comment" class="form-control" rows="3" maxlength="1000" title="Добавьте любую важную информацию о сделке"></textarea>
        </div>
       
    </fieldset>
    <button type="submit" class="btn btn-primary" title="Создать новую сделку на основе введенных данных">Создать сделку</button>
</form>

<!-- Подключение необходимых библиотек (jQuery и Select2) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Укажите корректный путь к вашему JSON-файлу
    var jsonFilePath = 'cities.json';

    // Загружаем JSON-файл
    $.getJSON(jsonFilePath, function(data) {
        // Группируем города по региону
        var groupedOptions = {};
        data.forEach(function(item) {
            var region = item.region;
            var city = item.city;
            if (!groupedOptions[region]) {
                groupedOptions[region] = [];
            }
            // Форматируем данные для Select2
            groupedOptions[region].push({
                id: city,
                text: city
            });
        });

        // Преобразуем сгруппированные данные в массив для Select2
        var select2Data = [];
        for (var region in groupedOptions) {
            select2Data.push({
                text: region,
                children: groupedOptions[region]
            });
        }

        // Инициализируем Select2 с полученными данными
        $('#client_timezone').select2({
            data: select2Data,
            placeholder: "-- Выберите город --",
            allowClear: true,
            // Добавляем обработчик для исправления ошибки aria-hidden
            dropdownParent: $('body')
        }).on('select2:open', function() {
            // Устраняем проблему доступности путем удаления aria-hidden
            setTimeout(function() {
                $('.select2-dropdown').parents('[aria-hidden="true"]').removeAttr('aria-hidden');
            }, 10);
        });
    })
    .fail(function(jqxhr, textStatus, error) {
        console.error("Ошибка загрузки JSON файла: " + textStatus + ", " + error);
    });

    // Инициализация всплывающих подсказок Bootstrap
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip({
            placement: 'auto',
            trigger: 'hover',
            delay: {show: 1000, hide: 100}, // Задержка в 1 секунду
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        });
    }
});

// Маска для поля "№ проекта"
$("input.maskproject").on("input", function() {
    var value = this.value;
    if (!value.startsWith("Проект ")) {
        value = "Проект " + value.replace(/[^0-9]/g, "");
    } else {
        var digits = value.substring(7).replace(/[^0-9]/g, "");
        digits = digits.substring(0, 4);
        value = "Проект " + digits;
    }
    this.value = value;
});
document.addEventListener("DOMContentLoaded", function () {
    var inputs = document.querySelectorAll("input.maskphone");
    for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];
        input.addEventListener("input", mask);
        input.addEventListener("focus", mask);
        input.addEventListener("blur", mask);
    }
    function mask(event) {
        var blank = "+_ (___) ___-__-__";
        var i = 0;
        var val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        this.value = blank.replace(/./g, function (char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        if (event.type == "blur") {
            if (this.value.length == 2) this.value = "";
        } else {
            setCursorPosition(this, this.value.length);
        }
    }
    function setCursorPosition(elem, pos) {
        elem.focus();
        if (elem.setSelectionRange) {
            elem.setSelectionRange(pos, pos);
            return;
        }
        if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.collapse(true);
            range.moveEnd("character", pos);
            range.moveStart("character", pos);
            range.select();
            return;
        }
    }
});
// Маска для поля "Пакет": разрешаем только одну цифру
$("#package").on("input", function() {
    var val = this.value.replace(/\D/g, "");
    if(val.length > 1) { val = val.substring(0, 1); }
    this.value = val;
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var formChanged = false;
        var form = document.getElementById("create-deal-form");
    
        // Отслеживаем изменения в форме
        form.addEventListener("input", function () {
            formChanged = true;
        });
    
        // Предупреждение при попытке закрытия вкладки или перезагрузки страницы
        window.addEventListener("beforeunload", function (event) {
            if (formChanged) {
                event.preventDefault();
                event.returnValue = "Вы уверены, что хотите покинуть страницу? Все несохраненные данные будут потеряны.";
            }
        });
    
        // Убираем предупреждение при отправке формы (если пользователь сохраняет данные)
        form.addEventListener("submit", function () {
            formChanged = false;
        });
    });
</script>

<style>
    /* Обеспечение правильного отображения select2 */
    body .select2-container--open {
        z-index: 9999 !important;
    }
    
    body .select2-dropdown {
        z-index: 10000 !important;
    }
    
    /* Предотвращаем проблемы с aria-hidden */
    .select2-hidden-accessible {
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        height: 1px !important;
        margin: -1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        width: 1px !important;
    }
</style>
