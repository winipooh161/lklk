
<div class="brifs" id="brifs">
    <h1 class="flex">Ваши сделки</h1>
    
    <!-- Кнопка-переключатель для фильтров -->
    <div class="filter-toggle" id="filter-toggle" data-target="#filter-panel" title="Нажмите, чтобы открыть или скрыть панель фильтров и поиска">
        <div class="filter-toggle-text">
            <i class="fas fa-filter"></i> Фильтры и поиск
            <span class="filter-counter" id="filter-counter" title="Количество активных фильтров">0</span>
        </div>
        <div class="filter-toggle-icon">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
    
    <div class="filter filter-panel" id="filter-panel">
        <form method="GET" action="{{ route('deal.cardinator') }}">
            <!-- Поисковое поле -->
            <div class="search">
              
                <!-- Панель фильтров -->
                <div class="filter-panels">
                    <div class="search__input search__input-styled">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Поиск по имени, телефону, email, № проекта..." 
                            title="Введите текст для поиска по данным сделок">
                    </div>
                    
                    <!-- Первая строка фильтров -->
                    <div class="filter-container">
                        <!-- Фильтр по статусу -->
                        <div class="filter-group">
                            <label class="filter-label" title="Фильтр сделок по их текущему статус"><i class="fas fa-tag"></i> Статус</label>
                            <div class="select-container">
                                <select name="status" class="filter-select" title="Выберите статус сделки для фильтрации">
                                    <option value="">Все статусы</option>
                                    @foreach ($statuses as $option)
                                        <option value="{{ $option }}" {{ request('status') === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        
                        <!-- Фильтр по пакету -->
                        <div class="filter-group">
                            <label class="filter-label" title="Фильтр по номеру пакета услуг"><i class="fas fa-box"></i> Пакет</label>
                            <div class="select-container">
                                <select name="package" class="filter-select" title="Выберите пакет услуг">
                                    <option value="">Все пакеты</option>
                                    <option value="1" {{ request('package') == '1' ? 'selected' : '' }} title="Базовый пакет услуг">Пакет 1</option>
                                    <option value="2" {{ request('package') == '2' ? 'selected' : '' }} title="Расширенный пакет услуг">Пакет 2</option>
                                    <option value="3" {{ request('package') == '3' ? 'selected' : '' }} title="Полный пакет услуг">Пакет 3</option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        
                        <!-- Фильтр по услуге из прайса -->
                        <div class="filter-group">
                            <label class="filter-label"><i class="fas fa-list-check"></i> Услуга</label>
                            <div class="select-container">
                                <select name="price_service_option" class="filter-select">
                                    <option value="">Все услуги</option>
                                    <option value="экспресс планировка" {{ request('price_service_option') == 'экспресс планировка' ? 'selected' : '' }}>Экспресс планировка</option>
                                    <option value="экспресс планировка с коллажами" {{ request('price_service_option') == 'экспресс планировка с коллажами' ? 'selected' : '' }}>Экспресс планировка с коллажами</option>
                                    <option value="экспресс проект с электрикой" {{ request('price_service_option') == 'экспресс проект с электрикой' ? 'selected' : '' }}>Экспресс проект с электрикой</option>
                                    <option value="экспресс планировка с электрикой и коллажами" {{ request('price_service_option') == 'экспресс планировка с электрикой и коллажами' ? 'selected' : '' }}>Экспресс планировка с электрикой и коллажами</option>
                                    <option value="экспресс проект с электрикой и визуализацией" {{ request('price_service_option') == 'экспресс проект с электрикой и визуализацией' ? 'selected' : '' }}>Экспресс проект с электрикой и визуализацией</option>
                                    <option value="экспресс рабочий проект" {{ request('price_service_option') == 'экспресс рабочий проект' ? 'selected' : '' }}>Экспресс рабочий проект</option>
                                    <option value="экспресс эскизный проект с рабочей документацией" {{ request('price_service_option') == 'экспресс эскизный проект с рабочей документацией' ? 'selected' : '' }}>Экспресс эскизный проект с рабочей документацией</option>
                                    <option value="экспресс 3Dвизуализация" {{ request('price_service_option') == 'экспресс 3Dвизуализация' ? 'selected' : '' }}>Экспресс 3Dвизуализация</option>
                                    <option value="экспресс полный дизайн-проект" {{ request('price_service_option') == 'экспресс полный дизайн-проект' ? 'selected' : '' }}>Экспресс полный дизайн-проект</option>
                                    <option value="360 градусов" {{ request('price_service_option') == '360 градусов' ? 'selected' : '' }}>360 градусов</option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                     
                        
                        @if(Auth::user()->status == 'admin' || Auth::user()->status == 'coordinator')
                        <!-- Фильтр по партнеру (только для админа и координатора) -->
                        <div class="filter-group">
                            <label class="filter-label"><i class="fas fa-user-tie"></i> Партнер</label>
                            <div class="select-container">
                                <select name="partner_id" class="filter-select">
                                    <option value="">Все партнеры</option>
                                    @foreach (\App\Models\User::where('status', 'partner')->orderBy('name')->get() as $partner)
                                        <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                            {{ $partner->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        @endif
                        
                    </div>
                    
                    <!-- Вторая строка фильтров -->
                    <div class="filter-container">
                           <!-- Фильтр по диапазону дат создания -->
                           <div class="filter-group">
                            <label class="filter-label" title="Фильтрация по периоду создания сделок"><i class="fas fa-calendar-alt"></i> Период создания</label>
                            <div class="date-filter-container">
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-day date-icon"></i>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Дата с" class="filter-date" 
                                           title="Дата начала периода поиска">
                                </div>
                                <span class="date-separator"><i class="fas fa-arrow-right"></i></span>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-day date-icon"></i>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Дата по" class="filter-date"
                                           title="Дата окончания периода поиска">
                                </div>
                            </div>
                        </div>
                        <!-- Сортировка по полям -->
                        <div class="filter-group">
                            <label class="filter-label" title="Выберите порядок сортировки сделок"><i class="fas fa-sort"></i> Сортировка</label>
                            <div class="select-container">
                                <select name="sort_by" class="filter-select" title="Выберите поле и порядок сортировки">
                                    <option value="">По умолчанию</option>
                                    <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }} title="Сортировка по имени от А до Я">Имя (А-Я)</option>
                                    <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }} title="Сортировка по имени от Я до А">Имя (Я-А)</option>
                                    <option value="created_date_asc" {{ request('sort_by') == 'created_date_asc' ? 'selected' : '' }} title="Сортировка по дате создания (от старых к новым)">Дата создания ↑</option>
                                    <option value="created_date_desc" {{ request('sort_by') == 'created_date_desc' ? 'selected' : '' }} title="Сортировка по дате создания (от новых к старым)">Дата создания ↓</option>
                                    <option value="total_sum_asc" {{ request('sort_by') == 'total_sum_asc' ? 'selected' : '' }} title="Сортировка по сумме (от меньшей к большей)">Сумма ↑</option>
                                    <option value="total_sum_desc" {{ request('sort_by') == 'total_sum_desc' ? 'selected' : '' }} title="Сортировка по сумме (от большей к меньшей)">Сумма ↓</option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Панель действий фильтра -->
            <div class="filter-actions">
                <button type="submit" class="filter-button" title="Применить выбранные фильтры">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ route('deal.cardinator') }}" class="filter-reset" title="Сбросить все фильтры и параметры поиска">
                    <i class="fas fa-undo"></i> Сбросить
                </a>
             
                <!-- Переключение вида отображения -->
                <div class="variate__view">
                    <button type="submit" name="view_type" value="blocks" title="Переключиться на отображение блоками"
                        class="view-button {{ $viewType === 'blocks' ? 'active-button' : '' }}">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="submit" name="view_type" value="table" title="Переключиться на отображение таблицей"
                        class="view-button {{ $viewType === 'table' ? 'active-button' : '' }}">
                        <i class="fas fa-table"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- JavaScript для подсчета активных фильтров и управления раскрывающимися фильтрами -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Используем глобальные функции из app.blade.php
        if (typeof updateFilterCounters === 'function') {
            updateFilterCounters();
        }
        
        // Обработчики для подсветки полей с фильтрами
        const dateFields = document.querySelectorAll('.filter-date');
        dateFields.forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });
            
            // Инициализация
            if (field.value) {
                field.classList.add('filter-active');
            }
        });
        
        // Подсветка селектов при изменении
        const selectFields = document.querySelectorAll('.filter-select');
        selectFields.forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });
            
            // Инициализация
            if (field.value) {
                field.classList.add('filter-active');
            }
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
</script>

<div class="deal" id="deal">
    <div class="deal__body">
        <div class="deal__cardinator__lists">
            @if ($viewType === 'table')
                <div class="table-container">
                    <table id="dealTable" class="deal-table display">
                        <thead>
                            <tr>
                                <th>Имя клиента</th>
                                <th>Номер клиента</th>
                                <th>Сумма сделки</th>
                                <th>Статус</th>
                                <!-- Добавляем новый заголовок -->
                                <th>Партнер</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody class="flex_table__format_table">
                            @foreach ($deals as $dealItem)
                                <tr>
                                    <td class="deal-name">{{ $dealItem->name }}</td>
                                    <td class="deal-phone">
                                        <a href="tel:{{ $dealItem->client_phone }}">
                                            {{ $dealItem->client_phone }}
                                        </a>
                                    </td>
                                    <td class="deal-sum">{{ $dealItem->total_sum ?? 'Отсутствует' }}</td>
                                    <td class="deal-status status-{{ strtolower(str_replace(' ', '-', $dealItem->status)) }}">{{ $dealItem->status }}</td>
                                    <!-- Новая колонка с информацией Офис/Партнер -->
                                    <td class="deal-partner">
                                        @if($dealItem->office_partner_id)
                                            <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                                {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                            </a>
                                        @else
                                            Не указан
                                        @endif
                                    </td>
                                    <td class="link__deistv">
                                        @if ($dealItem->registration_token)
                                            <a href="{{ $dealItem->registration_token ? route('register_by_deal', ['token' => $dealItem->registration_token]) : '' }}" 
                                               onclick="event.preventDefault(); copyRegistrationLink(this.href)" 
                                               title="Скопировать регистрационную ссылку">
                                                <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                            </a>
                                        @else
                                            <a href="#" title="Регистрационная ссылка отсутствует">
                                                <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                            </a>
                                        @endif
                                        
                                        <a href="{{ $dealItem->link ? url($dealItem->link) : '#' }}" title="Бриф">
                                            <img src="/storage/icon/brif.svg" alt="Бриф">
                                        </a>
                                        
                                        @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                                            <a href="{{ route('deal.change_logs.deal', ['deal' => $dealItem->id]) }}" 
                                               title="Логи сделки">
                                                <img src="/storage/icon/log.svg" alt="Логи">
                                            </a>
                                        @endif
                                        
                                        @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                            <button type="button" class="edit-deal-btn" data-id="{{ $dealItem->id }}" title="Редактировать сделку">
                                                <img src="/storage/icon/add.svg" alt="Редактировать">
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Блочный вид -->
                <div class="faq__body__deal" id="all-deals-container">
                    <h4 class="flex">Все сделки</h4>
                    @if ($deals->isEmpty())
                        <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal" onclick="window.location.href='{{ route('deals.create') }}'">
                            @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <button >
                                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal" onclick="window.location.href='{{ route('deals.create') }}'">
                            @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <button onclick="window.location.href='{{ route('deals.create') }}'">
                                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                                </button>
                            @endif
                        </div>
                        @foreach ($deals as $dealItem)
                            <div class="faq_block__deal" data-id="{{ $dealItem->id }}" data-status="{{ $dealItem->status }}">
                                <div class="faq_item__deal">
                                    <div class="faq_question__deal flex between">
                                        <div class="faq_question__deal__info">

                                            @if ($dealItem->avatar_path)
                                                <div class="deal__avatar deal__avatar__cardinator">
                                                    <img src="{{ asset('storage/' . $dealItem->avatar_path) }}"
                                                        alt="Avatar">
                                                </div>
                                            @endif
                                            <div class="deal__cardinator__info">
                                                <div class="ctatus__deal___info">
                                                    <div class="div__status_info">{{ $dealItem->status }}</div>
                                                </div>
                                                <h4>{{ $dealItem->name }}</h4>
                                                <p class="doptitle">
                                                <h4>{{ $dealItem->project_number }}</h4>
                                                </p>
                                                <p>Телефон:
                                                    <a href="tel:{{ $dealItem->client_phone }}">
                                                        {{ $dealItem->client_phone }}
                                                    </a>
                                                </p>
                                                <!-- Добавляем информацию Офис/Партнер -->
                                                <p>Партнер:
                                                    @if($dealItem->office_partner_id)
                                                        <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                                            {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                                        </a>
                                                    @else
                                                        Не указан
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <ul>
                                            <li>
                                                @php
                                                    // Убираем переменную $groupChat
                                                @endphp
                                            
                                            </li>
                                            <li>
                                                @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                                    <button type="button" class="edit-deal-btn"
                                                        data-id="{{ $dealItem->id }}">
                                                        <img src="/storage/icon/create__blue.svg" alt="">
                                                        <span>Изменить</span>
                                                    </button>
                                                @endif

                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div class="pagination" id="all-deals-pagination"></div>
                </div>
            @endif
        </div>
    </div>
</div>
<div id="dealModalContainer"></div>



<script>
    $(function() {
        // Инициализация DataTable для табличного вида
        if ($('#dealTable').length) {
            $('#dealTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
                },
                paging: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                dom: '<"table-header"<"table-title"l><"table-search"f>><"table-content"rt><"table-footer"<"table-info"i><"table-pagination"p>>',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Все"]]
            });
            
            // Добавляем обработчик для окрашивания ячеек статуса
            $('#dealTable tbody tr').each(function() {
                var statusCell = $(this).find('td:nth-child(4)');
                var status = statusCell.text().trim();
                
                // Добавляем нужный класс в зависимости от статуса
                if (status === 'Новая заявка') {
                    statusCell.addClass('status-new');
                } else if (status === 'В процессе') {
                    statusCell.addClass('status-processing');
                } else if (status === 'Проект завершен') {
                    statusCell.addClass('status-completed');
                }
            });
        }

        // Пагинация для блочного вида
        function paginateContainer(container, paginationContainer, perPage = 6) {
            var $container = $(container);
            var $blocks = $container.find('.faq_block__deal');
            var total = $blocks.length;

            if (total <= perPage) {
                $blocks.show();
                return;
            }

            $blocks.hide();
            $blocks.slice(0, perPage).show();

            $(paginationContainer).pagination({
                items: total,
                itemsOnPage: perPage,
                cssStyle: 'light-theme',
                prevText: 'Предыдущая',
                nextText: 'Следующая',
                onPageClick: function(pageNumber, event) {
                    var start = (pageNumber - 1) * perPage;
                    var end = start + perPage;
                    $blocks.hide().slice(start, end).show();
                }
            });
        }

        // Вызов функции пагинации для блочного представления
        paginateContainer('#all-deals-container', '#all-deals-pagination', 6);

        var $editModal = $('#editModal'),
            $editForm = $('#editForm');

        // Функция инициализации Select2, вызывается после загрузки модального окна
        function initSelect2() {
            $('.select2-field').select2({
                width: '100%',
                placeholder: "Выберите значение",
                allowClear: true,
                dropdownParent: $('#editModal')
            });
        }

        var modalCache = {}; // Объект для кэширования модальных окон

        // Обработчик клика для открытия модального окна с данными сделки
        $('.edit-deal-btn').on('click', function() {
            var dealId = $(this).data('id');
            var $modalContainer = $("#dealModalContainer");

            // Проверяем, есть ли модальное окно в кэше
            if (modalCache[dealId]) {
                // Если есть, показываем его из кэша
                $modalContainer.html(modalCache[dealId]);
                initSelect2();
                $("#editModal").show().addClass('show');
                initModalFunctions();
            } else {
                // Если нет, загружаем с сервера
                // Показываем индикатор загрузки
                $modalContainer.html('<div class="loading">Загрузка...</div>');

                $.ajax({
                    url: "/deal/" + dealId + "/modal",
                    type: "GET",
                    success: function(response) {
                        // Сохраняем модальное окно в кэш
                        modalCache[dealId] = response.html;

                        // Вставляем HTML модального окна
                        $modalContainer.html(response.html);

                        // Инициализируем Select2 для dropdowns
                        initSelect2();

                        // Показываем модальное окно
                        $("#editModal").show().addClass('show');

                        // Обработчики закрытия модального окна
                        $('#closeModalBtn').on('click', function() {
                            $("#editModal").removeClass('show').hide();
                        });

                        $("#editModal").on('click', function(e) {
                            if (e.target === this) $(this).removeClass('show')
                            .hide();
                        });

                        // Инициализация других JS-функций для модального окна
                        initModalFunctions();
                    },
                    error: function(xhr, status, error) {
                        console.error("Ошибка загрузки данных сделки:", status, error);
                        alert(
                            "Ошибка загрузки данных сделки. Попробуйте обновить страницу.");
                    },
                    complete: function() {
                        // Скрываем индикатор загрузки
                        $('.loading').remove();
                    }
                });
            }

            // Динамическое изменение URL
            history.pushState(null, null, "#editDealModal");
        });

        // Обработчик закрытия модального окна
        $('#dealModalContainer').on('click', '#closeModalBtn', function() {
            $("#editModal").removeClass('show').hide();
            history.pushState("", document.title, window.location.pathname + window.location.search);
        });

        $('#dealModalContainer').on('click', '#editModal', function(e) {
            if (e.target === this) {
                $(this).removeClass('show').hide();
                history.pushState("", document.title, window.location.pathname + window.location
                .search);
            }
        });

        // Функция инициализации дополнительных JS-функций для модального окна
        function initModalFunctions() {
            var modules = $("#editModal fieldset.module__deal");
            var buttons = $("#editModal .button__points button");

            // Настройка переключения между вкладками
            modules.css({
                display: "none",
                opacity: "0",
                transition: "opacity 0.3s ease-in-out"
            });

            // Показываем активную вкладку (Лента)
            var activeModule = $("#module-zakaz");
            activeModule.css({
                display: "flex"
            });

            setTimeout(function() {
                activeModule.css({
                    opacity: "1"
                });
            }, 10);

            // Обработчик нажатия на кнопки вкладок
            buttons.on('click', function() {
                var targetText = $(this).data('target').trim();
                buttons.removeClass("buttonSealaActive");
                $(this).addClass("buttonSealaActive");

                modules.css({
                    opacity: "0"
                });

                setTimeout(function() {
                    modules.css({
                        display: "none"
                    });
                }, 300);

                setTimeout(function() {
                    modules.each(function() {
                        var legend = $(this).find("legend").text().trim();
                        if (legend === targetText) {
                            $(this).css({
                                display: "flex"
                            });
                            setTimeout(function() {
                                $(this).css({
                                    opacity: "1"
                                });
                            }.bind(this), 10);
                        }
                    });
                }, 300);
            });

            // Обработчик отправки формы ленты
            $("#feed-form").on("submit", function(e) {
                e.preventDefault();
                var content = $("#feed-content").val().trim();
                if (!content) {
                    alert("Введите текст сообщения!");
                    return;
                }
                var dealId = $("#dealIdField").val();
                if (dealId) {
                    $.ajax({
                        url: "/deal/" + dealId + "/feed",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            content: content
                        },
                        success: function(response) {
                            // ...existing code...
                        },
                        error: function(xhr) {
                            alert("Ошибка при добавлении записи: " + xhr.responseText);
                        }
                    });
                } else {
                    alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
                }
            });

            // Обработчик для файловых полей
            $('input[type="file"]').on('change', function() {
                var file = this.files[0];
                var fileName = file ? file.name : "";
                var fieldName = $(this).attr('id');
                var linkDiv = $('#' + fieldName + 'Link');

                if (fileName) {
                    linkDiv.html('<a href="' + URL.createObjectURL(file) + '" target="_blank">' +
                        fileName + '</a>');
                }
            });
        }

        $('#closeModalBtn').on('click', function() {
            $("#editModal").removeClass('show').hide();
        });
        $("#editModal").on('click', function(e) {
            if (e.target === this) $(this).removeClass('show').hide();
        });

      

        var modules = $("#editModal fieldset.module__deal");
        var buttons = $("#editModal .button__points button");
        modules.css({
            display: "none",
            opacity: "0",
            transition: "opacity 0.3s ease-in-out"
        });
        if (modules.length > 0) {
            $(modules[0]).css({
                display: "flex"
            });
            setTimeout(function() {
                $(modules[0]).css({
                    opacity: "1"
                });
            }, 10);
        }
        buttons.on('click', function() {
            var targetText = $(this).data('target').trim();
            buttons.removeClass("buttonSealaActive");
            $(this).addClass("buttonSealaActive");
            modules.css({
                opacity: "0"
            });
            setTimeout(function() {
                modules.css({
                    display: "none"
                });
            }, 300);
            setTimeout(function() {
                modules.each(function() {
                    var legend = $(this).find("legend").text().trim();
                    if (legend === targetText) {
                        $(this).css({
                            display: "flex"
                        });
                        setTimeout(function() {
                            $(this).css({
                                opacity: "1"
                            });
                        }.bind(this), 10);
                    }
                });
            }, 300);
        });

        $.getJSON('/cities.json', function(data) {
            var grouped = {};
            $.each(data, function(i, item) {
                grouped[item.region] = grouped[item.region] || [];
                grouped[item.region].push({
                    id: item.city,
                    text: item.city
                });
            });
            var selectData = $.map(grouped, function(cities, region) {
                return {
                    text: region,
                    children: cities
                };
            });
            $('#client_timezone, #cityField').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --", // Изменён placeholder
                allowClear: true,
                minimumInputLength: 1, // Включён поиск по городам
                dropdownParent: $('#editModal')
            });
            // Инициализация select2 для поля "client_city" с обработкой поиска при вводе от 1 символа
            $('#client_city').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --",
                allowClear: true,
                minimumInputLength: 1,
                dropdownParent: $('#editModal')
            });
        }).fail(function(err) {
            console.error("Ошибка загрузки городов", err);
        });

        $('#responsiblesField').select2({
            placeholder: "Выберите ответственных",
            allowClear: true,
            dropdownParent: $('#editModal')
        });
        $('.select2-field').select2({
            width: '100%',
            placeholder: "Выберите значение",
            allowClear: true,
            dropdownParent: $('#editModal')
        });

        $("#feed-form").on("submit", function(e) {
            e.preventDefault();
            var content = $("#feed-content").val().trim();
            if (!content) {
                alert("Введите текст сообщения!");
                return;
            }
            var dealId = $("#dealIdField").val();
            if (dealId) {
                $.ajax({
                    url: "{{ url('/deal') }}/" + dealId + "/feed",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        content: content
                    },
                    success: function(response) {
                        $("#feed-content").val("");
                        var avatarUrl = response.avatar_url ? response.avatar_url :
                            "/storage/group_default.svg";
                        $("#feed-posts-container").prepend(`
                        <div class="feed-post">
                            <div class="feed-post-avatar">
                                <img src="${avatarUrl}" alt="${response.user_name}">
                            </div>
                            <div class="feed-post-text">
                                <div class="feed-author">${response.user_name}</div>
                                <div class="feed-content">${response.content}</div>
                                <div class="feed-date">${response.date}</div>
                            </div>
                        </div>
                    `);
                    },
                    error: function(xhr) {
                        alert("Ошибка при добавлении записи: " + xhr.responseText);
                    }
                });
            } else {
                alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
            }
        });

        $('input[type="file"]').on('change', function() {
            var file = this.files[0];
            var fileName = file ? file.name : "";
            var linkId = $(this).attr('id') + "FileName";
            if (fileName) {
                $('#' + linkId)
                    .text(fileName)
                    .attr('href', URL.createObjectURL(file))
                    .show();
            } else {
                $('#' + linkId).hide();
            }
        });
    });

    function copyRegistrationLink(regUrl) {
        if (regUrl) {
            navigator.clipboard.writeText(regUrl).then(function() {
                alert('Регистрационная ссылка скопирована: ' + regUrl);
            }).catch(function(err) {
                console.error('Ошибка копирования ссылки: ', err);
            });
        } else {
            alert('Регистрационная ссылка отсутствует.');
        }
    }
</script>
<script>
    $(function() {
        // ...existing code...
        
        // Обработчик отправки формы редактирования сделки с поддержкой AJAX
        $('#dealModalContainer').on('submit', '#editForm', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $("#editModal").removeClass('show').hide();
                    
                    if (response.success) {
                        // Показываем сообщение об успехе
                        $('<div class="success-message">Сделка успешно обновлена</div>')
                            .appendTo('body')
                            .fadeIn('fast')
                            .delay(3000)
                            .fadeOut('slow', function() { $(this).remove(); });
                        
                        // Если статус изменен на "Проект завершен", проверяем необходимость оценок
                        if (response.status_changed_to_completed || 
                            (response.deal && response.deal.status === 'Проект завершен')) {
                            // Вызываем событие обновления сделки
                            window.dispatchEvent(new CustomEvent('dealUpdated', { 
                                detail: { dealId: response.deal.id }
                            }));
                            
                            // Непосредственно вызываем функцию проверки оценок
                            if (typeof window.checkPendingRatings === 'function') {
                                setTimeout(() => {
                                    console.log('Проверка необходимости оценок для сделки:', response.deal.id);
                                    window.checkPendingRatings(response.deal.id);
                                }, 1000);
                            }
                        }
                        
                        // Обновляем страницу через 1 секунду
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    alert('Произошла ошибка при обновлении сделки.');
                    console.error(xhr.responseText);
                }
            });
        });
        
        // ...existing code...
    });
    
    // ...existing code...
</script>

<!-- Добавляем код для проверки завершенных сделок при загрузке страницы -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Небольшая задержка для уверенности, что ratings.js загружен
        setTimeout(function() {
            if (typeof window.checkPendingRatings !== 'function') {
                console.error('[Сделки] Функция checkPendingRatings не определена!');
                return;
            }

            console.log('[Сделки] Поиск завершенных сделок для проверки оценок...');
            
            // Собираем ID завершенных сделок
            const completedDealIds = [];
            
            // Проверяем блочное представление
            document.querySelectorAll('.faq_block__deal[data-status="Проект завершен"]').forEach(block => {
                const dealId = block.dataset.id;
                if (dealId) completedDealIds.push(dealId);
            });
            
            // Проверяем табличное представление
            document.querySelectorAll('#dealTable td').forEach(cell => {
                if (cell.textContent.trim() === 'Проект завершен') {
                    const row = cell.closest('tr');
                    const editBtn = row.querySelector('.edit-deal-btn');
                    if (editBtn && editBtn.dataset.id) {
                        completedDealIds.push(editBtn.dataset.id);
                    }
                }
            });
            
            console.log('[Сделки] Найдено завершенных сделок:', completedDealIds.length);
            
            // Проверяем localStorage
            const completedDealId = localStorage.getItem('completed_deal_id');
            if (completedDealId) {
                console.log('[Сделки] Найден ID завершенной сделки в localStorage:', completedDealId);
                window.checkPendingRatings(completedDealId);
                localStorage.removeItem('completed_deal_id');
            }
            // Если есть завершенные сделки на странице, проверяем первую из них
            else if (completedDealIds.length > 0) {
                console.log('[Сделки] Проверка оценок для первой найденной сделки:', completedDealIds[0]);
                window.checkPendingRatings(completedDealIds[0]);
            }
        }, 800);
    });
</script>
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-selection--multiple {
        min-height: 38px !important;
    }

    .select2-selection__choice {
        padding: 2px 5px !important;
        margin: 2px !important;
        background-color: #e4e4e4 !important;
        border: none !important;
        border-radius: 3px !important;
    }
</style>
