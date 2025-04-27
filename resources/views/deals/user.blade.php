<div class="deals-list deals-list-user">
    <h1>Ваши сделки 
        @if($userDeals->isNotEmpty())
            <p class="status__user__deal" title="Текущий статус вашей первой сделки">{{ $userDeals->first()->status }}</p>
        @endif
    </h1>
    <!-- Добавляем раскрывающийся фильтр для сделок пользователя -->
    @if($userDeals->count() > 1)
    <div class="filter-toggle" id="user-deals-filter-toggle" data-target="#user-deals-filter-panel" title="Нажмите, чтобы открыть/закрыть панель фильтров">
        <div class="filter-toggle-text">
            <i class="fas fa-filter"></i> Фильтры
            <span class="filter-counter" id="user-deals-filter-counter" title="Количество активных фильтров">0</span>
        </div>
        <div class="filter-toggle-icon">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
    <div class="filter filter-panel" id="user-deals-filter-panel">
        <form method="GET" action="{{ url()->current() }}">
            <div class="filter-container">
                <div class="filter-group">
                    <label class="filter-label" title="Фильтрация сделок по статусу"><i class="fas fa-tag"></i> Статус</label>
                    <div class="select-container">
                        <select name="status" class="filter-select" title="Выберите статус сделки для фильтрации">
                            <option value="">Все статусы</option>
                            @foreach($userDeals->pluck('status')->unique() as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down select-icon"></i>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label" title="Поиск сделок по ключевым словам"><i class="fas fa-search"></i> Поиск</label>
                    <div class="search__input search__input-styled">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по названию..." title="Введите текст для поиска по названию сделки">
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="filter-button" title="Применить выбранные фильтры">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ url()->current() }}" class="filter-reset" title="Сбросить все фильтры и вернуться к полному списку">
                    <i class="fas fa-undo"></i> Сбросить
                </a>
            </div>
        </form>
    </div>
    @endif
    
    @if ($userDeals->isNotEmpty())
        @php
            // Фильтрация сделок на стороне шаблона, если пришли параметры фильтрации
            $filteredDeals = $userDeals;
            
            if(request('status')) {
                $filteredDeals = $filteredDeals->where('status', request('status'));
            }
            
            if(request('search')) {
                $search = strtolower(request('search'));
                $filteredDeals = $filteredDeals->filter(function($deal) use ($search) {
                    return str_contains(strtolower($deal->name), $search) || 
                           str_contains(strtolower($deal->description ?? ''), $search);
                });
            }
        @endphp
        
        @foreach ($filteredDeals as $deal)
            <div class="deal" id="deal-{{ $deal->id }}" data-id="{{ $deal->id }}" data-status="{{ $deal->status }}">
                <div class="deal__body">
                    <!-- Информация о сделке -->
                    <div class="deal__info">
                        <div class="deal__info__profile">
                            <div class="deal__avatar" title="Аватар сделки">
                                <img src="{{ asset('storage/' . ($deal->avatar_path ?? 'avatars/group_default.svg')) }}" alt="Avatar">
                            </div>
                            <div class="deal__info__title">
                                <h3 title="Название сделки">{{ $deal->name }}</h3>
                                <div class="deal__meta">
                                    <span class="deal__id" title="Номер проекта">
                                        <i class="fas fa-hashtag"></i> 
                                        {{ $deal->project_number ?? 'Проект #'.$deal->id }}
                                    </span>
                                 
                                   
                                </div>
                            </div>
                        </div>
                        <div class="deal__status">
                            <h3><p title="Общая сумма сделки">Сумма сделки:</p> {{ number_format($deal->total_sum ?? 0, 0, ',', ' ') }} ₽</h3>
                            <div class="deal__status-button">
                                @if ($deal->link)
                                <div class="brif__link__container">
                                    <a href="{{ $deal->link }}" class="brif-link" title="Перейти к брифу сделки">
                                        <i class="fas fa-file-alt"></i> Смотреть бриф
                                    </a>
                                </div>
                                @else
                                <p class="no-brif" title="Бриф для этой сделки еще не создан">
                                    <i class="fas fa-exclamation-circle"></i> Бриф не прикреплен
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Улучшенная информационная сетка о сделке -->
                    <div class="deal__details">
                        <div class="deal__progress">
                            <h4 class="details-title">Статус выполнения</h4>
                            @php
                                $stages = [
                                    'Ждем ТЗ' => 0,
                                    'Планировка' => 20,
                                    'Коллажи' => 40,
                                    'Визуализация' => 60,
                                    'Рабочка/сбор ИП' => 80,
                                    'Проект готов' => 90,
                                    'Проект завершен' => 100,
                                    'Проект на паузе' => -1 // Особый случай
                                ];
                                
                                $currentStage = $deal->status;
                                $progress = isset($stages[$currentStage]) ? $stages[$currentStage] : 0;
                                $isPaused = $currentStage === 'Проект на паузе';
                            @endphp
                            
                            <div class="progress-container">
                                @if($isPaused)
                                    <div class="progress-paused">
                                        <i class="fas fa-pause-circle"></i> Проект временно на паузе
                                    </div>
                                @else
                                    <div class="progress-bar-wrapper">
                                        <div class="progress-bar" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <div class="progress-text">{{ $progress }}% выполнено</div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Обновленная информационная сетка -->
                        <div class="deal__info-grid">
                            @if($deal->package)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-box"></i> Пакет услуг</span>
                                <span class="info-value">{{ $deal->package }}</span>
                            </div>
                            @endif
                            
                            @if($deal->price_service_option)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-clipboard-list"></i> Услуга</span>
                                <span class="info-value">{{ $deal->price_service_option }}</span>
                            </div>
                            @endif
                            
                            @if($deal->rooms_count_pricing)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-door-open"></i> Количество комнат</span>
                                <span class="info-value">{{ $deal->rooms_count_pricing }}</span>
                            </div>
                            @endif
                            
                            @if($deal->client_city)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-city"></i> Город</span>
                                <span class="info-value">{{ $deal->client_city }}</span>
                            </div>
                            @endif
                            
                            @if($deal->start_date)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-day"></i> Дата начала работ</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($deal->start_date)->format('d.m.Y') }}</span>
                            </div>
                            @endif
                            
                            @if($deal->project_end_date)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-check"></i> Плановая дата завершения</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($deal->project_end_date)->format('d.m.Y') }}</span>
                            </div>
                            @endif
                            
                            @if($deal->project_duration)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-hourglass-half"></i> Длительность работ</span>
                                <span class="info-value">{{ $deal->project_duration }} рабочих дней</span>
                            </div>
                            @endif
                            
                            @if($deal->execution_order_comment)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-comment"></i> Комментарий к заказу</span>
                                <span class="info-value">{{ $deal->execution_order_comment }}</span>
                            </div>
                            @endif
                            
                            @if($deal->total_sum)
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-ruble-sign"></i> Стоимость проекта</span>
                                <span class="info-value">{{ number_format($deal->total_sum ?? 0, 0, ',', ' ') }} ₽</span>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Блок с файлами проекта, если они есть -->
                        <div class="deal__files">
                            @php
                                $hasFiles = false;
                                $files = [];
                                
                                if($deal->final_floorplan) {
                                    $hasFiles = true;
                                    $files[] = [
                                        'name' => 'Планировка',
                                        'path' => $deal->final_floorplan,
                                        'icon' => 'fas fa-map'
                                    ];
                                }
                                
                                if($deal->final_collage) {
                                    $hasFiles = true;
                                    $files[] = [
                                        'name' => 'Коллаж',
                                        'path' => $deal->final_collage,
                                        'icon' => 'fas fa-object-group'
                                    ];
                                }
                                
                                if($deal->final_project_file) {
                                    $hasFiles = true;
                                    $files[] = [
                                        'name' => 'Итоговый проект',
                                        'path' => $deal->final_project_file,
                                        'icon' => 'fas fa-file-pdf'
                                    ];
                                }
                                
                                if($deal->work_act && $deal->status == 'Проект завершен') {
                                    $hasFiles = true;
                                    $files[] = [
                                        'name' => 'Акт выполненных работ',
                                        'path' => $deal->work_act,
                                        'icon' => 'fas fa-file-signature'
                                    ];
                                }
                                
                                if($deal->visualization_link) {
                                    $hasFiles = true;
                                    $files[] = [
                                        'name' => 'Визуализация',
                                        'path' => $deal->visualization_link,
                                        'icon' => 'fas fa-eye',
                                        'is_link' => true
                                    ];
                                }
                            @endphp
                            
                            @if($hasFiles)
                                <h4 class="details-title">Файлы проекта</h4>
                                <div class="files-grid">
                                    @foreach($files as $file)
                                        <div class="file-item">
                                            <div class="file-icon">
                                                <i class="{{ $file['icon'] }}"></i>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name">{{ $file['name'] }}</span>
                                                @if(isset($file['is_link']) && $file['is_link'])
                                                    <a href="{{ $file['path'] }}" target="_blank" class="file-link">
                                                        <i class="fas fa-external-link-alt"></i> Открыть
                                                    </a>
                                                @else
                                                    <a href="{{ asset('storage/'.$file['path']) }}" target="_blank" class="file-link">
                                                        <i class="fas fa-download"></i> Скачать
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="deal__container deal__container__modul">
                        <!-- Исправленная секция отображения команды проекта -->
                        <div class="deal__responsible">
                            <h4 class="responsible-title"><i class="fas fa-users"></i> Команда проекта</h4>
                            <ul>
                                @php
                                    // Получим всех участников сделки с разными ролями
                                    $teamMembers = collect();
                                    
                                    // Координатор сделки
                                    if($deal->coordinator_id) {
                                        $coordinator = App\Models\User::find($deal->coordinator_id);
                                        if($coordinator) {
                                            $teamMembers->push([
                                                'id' => $coordinator->id,
                                                'name' => $coordinator->name,
                                                'role' => 'Координатор',
                                                'avatar' => $coordinator->avatar_url ?? 'storage/avatars/default-avatar.png',
                                                'order' => 1 // Для сортировки
                                            ]);
                                        }
                                    }
                                    
                                    // Архитектор
                                    if($deal->architect_id) {
                                        $architect = App\Models\User::find($deal->architect_id);
                                        if($architect) {
                                            $teamMembers->push([
                                                'id' => $architect->id,
                                                'name' => $architect->name,
                                                'role' => 'Архитектор',
                                                'avatar' => $architect->avatar_url ?? 'storage/avatars/default-avatar.png',
                                                'order' => 2
                                            ]);
                                        }
                                    }
                                    
                                    // Дизайнер
                                    if($deal->designer_id) {
                                        $designer = App\Models\User::find($deal->designer_id);
                                        if($designer) {
                                            $teamMembers->push([
                                                'id' => $designer->id,
                                                'name' => $designer->name,
                                                'role' => 'Дизайнер',
                                                'avatar' => $designer->avatar_url ?? 'storage/avatars/default-avatar.png',
                                                'order' => 3
                                            ]);
                                        }
                                    }
                                    
                                    // Визуализатор
                                    if($deal->visualizer_id) {
                                        $visualizer = App\Models\User::find($deal->visualizer_id);
                                        if($visualizer) {
                                            $teamMembers->push([
                                                'id' => $visualizer->id,
                                                'name' => $visualizer->name,
                                                'role' => 'Визуализатор',
                                                'avatar' => $visualizer->avatar_url ?? 'storage/avatars/default-avatar.png',
                                                'order' => 4
                                            ]);
                                        }
                                    }
                                    
                                    // Партнер
                                    if($deal->office_partner_id) {
                                        $partner = App\Models\User::find($deal->office_partner_id);
                                        if($partner) {
                                            $teamMembers->push([
                                                'id' => $partner->id,
                                                'name' => $partner->name,
                                                'role' => 'Партнер',
                                                'avatar' => $partner->avatar_url ?? 'storage/avatars/default-avatar.png',
                                                'order' => 5
                                            ]);
                                        }
                                    }
                                    
                                    // Сортировка по порядку ролей
                                    $teamMembers = $teamMembers->sortBy('order');
                                @endphp
                                
                                @if($teamMembers->count() > 0)
                                    @foreach($teamMembers as $member)
                                    <li onclick="window.location.href='/profile/view/{{ $member['id'] }}'" class="deal__responsible__user" title="Нажмите, чтобы просмотреть профиль {{ $member['name'] }}">
                                        <div class="deal__responsible__avatar">
                                            <img src="{{ asset($member['avatar']) }}" alt="Аватар {{ $member['name'] }}">
                                        </div>
                                        <div class="deal__responsible__info">
                                            <h5>{{ $member['name'] }}</h5>
                                            <p title="Роль в проекте">{{ $member['role'] }}</p>
                                        </div>
                                    </li>
                                    @endforeach
                                @else
                                    <li class="deal__responsible__user">
                                        <p title="За сделку пока никто не назначен ответственным">Ответственные не назначены</p>
                                    </li>
                                @endif
                            </ul>
                               <!-- Новый блок FAQ (вопрос-ответ) -->
                        <div class="deal__faq">
                            <h4 class="faq-title"><i class="fas fa-question-circle"></i> Часто задаваемые вопросы</h4>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    Какие этапы проходит мой дизайн-проект?
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        Дизайн-проект обычно проходит следующие этапы: планировка, подбор материалов и коллажи, визуализация, 
                                        разработка рабочей документации, финальная доработка и выдача готового проекта. Текущий статус вашего
                                        проекта всегда отображается в этом интерфейсе.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    Как я могу внести изменения в проект?
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        Все пожелания по изменениям необходимо направлять координатору проекта через чат сделки. 
                                        После обсуждения команда внесет необходимые корректировки согласно условиям договора.
                                        Обращаем внимание, что количество правок может быть ограничено выбранным тарифом.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    Что делать, если я хочу изменить объем работ?
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        Если вы хотите изменить объем работ, добавить дополнительные услуги или изменить текущий пакет,
                                        свяжитесь с вашим координатором через чат сделки. Он проконсультирует вас по всем вопросам 
                                        и поможет скорректировать проект с учетом новых пожеланий.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    Как получить готовые файлы проекта?
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        По мере завершения этапов все готовые файлы будут появляться в разделе "Файлы проекта" на этой странице.
                                        После полного завершения проекта вы сможете скачать все материалы одним архивом. Также координатор
                                        проекта может выслать вам дополнительные материалы в чате.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    Как связаться с командой проекта?
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        Для связи с командой проекта используйте чат сделки. Нажмите на кнопку "Перейти в чат проекта" 
                                        внизу страницы. Там вы сможете общаться со всеми участниками процесса и задавать интересующие 
                                        вас вопросы.
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        
                     
                    </div><!-- /.deal__container -->
                    
                    <!-- Кнопки действий -->
                    <div class="deal__actions">
                        @if($deal->status === 'Проект завершен')
                            <button type="button" class="btn btn-rating check-ratings-btn" data-deal-id="{{ $deal->id }}" title="Оценить работу участников проекта">
                                <i class="fas fa-star"></i> Оценить специалистов
                            </button>
                        @endif
                    </div>
                </div><!-- /.deal__body -->
            </div><!-- /.deal -->
        @endforeach
    @else
        <div class="no-deals">
            <div class="no-deals-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3>У вас пока нет сделок</h3>
            <p>Когда вы заключите сделку, она появится здесь.</p>
        </div>
    @endif
</div>

<!-- Модальное окно для чата -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация раскрывающихся фильтров
        initCollapsibleFilters();
        
        // Небольшая задержка для уверенности, что ratings.js загружен
        setTimeout(function() {
            if (typeof window.checkPendingRatings !== 'function') {
                console.error('[Мои сделки] Функция checkPendingRatings не определена!');
                return;
            }
            
            console.log('[Мои сделки] Поиск завершенных сделок для проверки оценок...');
            
            // Находим завершенные сделки
            const completedDeals = document.querySelectorAll('.deal[data-status="Проект завершен"]');
            console.log('[Мои сделки] Найдено завершенных сделок:', completedDeals.length);
            
            // Проверяем localStorage
            const storedDealId = localStorage.getItem('completed_deal_id');
            if (storedDealId) {
                console.log('[Мои сделки] Найден ID завершенной сделки в localStorage:', storedDealId);
                window.checkPendingRatings(storedDealId);
                localStorage.removeItem('completed_deal_id');
            }
            // Если есть завершенные сделки, проверяем первую
            else if (completedDeals.length > 0) {
                const dealId = completedDeals[0].getAttribute('data-id');
                if (dealId) {
                    console.log('[Мои сделки] Проверка оценок для сделки:', dealId);
                    window.checkPendingRatings(dealId);
                }
            }
        }, 800);

        // Инициализация кнопок для проверки оценок
        const ratingButtons = document.querySelectorAll('.check-ratings-btn');
        ratingButtons.forEach(button => {
            button.addEventListener('click', function() {
                const dealId = this.getAttribute('data-deal-id');
                if (typeof window.checkPendingRatings === 'function') {
                    window.checkPendingRatings(dealId);
                } else {
                    console.error('Функция checkPendingRatings не определена!');
                }
            });
        });

        // Вызываем обновление счетчиков фильтров после загрузки страницы
        if (typeof updateFilterCounters === 'function') {
            updateFilterCounters();
        }
        
        // Инициализация всплывающих подсказок Bootstrap
        if (typeof $().tooltip === 'function') {
            $('[title]').tooltip({
                placement: 'auto',
                trigger: 'hover',
                delay: {show: 1000, hide: 100}, // Задержка в 1 секунду
                template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });
        }

        // Инициализация FAQ
        const faqQuestions = document.querySelectorAll('.faq-question');
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                // Закрываем все ответы
                const allFaqItems = document.querySelectorAll('.faq-item');
                allFaqItems.forEach(item => {
                    if(item !== this.parentElement) {
                        item.classList.remove('active');
                        item.querySelector('.faq-question').classList.remove('active');
                    }
                });
                
                // Переключаем активное состояние
                const faqItem = this.parentElement;
                faqItem.classList.toggle('active');
                this.classList.toggle('active');
            });
        });
    });
</script>

<style>

</style>

<!-- Скрипт для плавной анимации прогресс-бара при загрузке -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Анимируем прогресс-бары
        const progressBars = document.querySelectorAll('.progress-bar');
        
        setTimeout(() => {
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        }, 400);

        // ...existing code...
    });
</script>

