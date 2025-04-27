<div class="profile-container">
    <div class="profile-header">
        <h1 class="profile-title">Личный профиль</h1>
        <p class="profile-subtitle">Управление персональными данными и настройками аккаунта</p>
    </div>

    <div class="profile-grid">
        <!-- Левая панель профиля -->
        <div class="profile-sidebar">
            <div class="profile-user">
                <div class="profile-avatar-wrapper">
                    <img src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('storage/avatars/group_default.svg') }}" alt="Аватар" class="profile-avatar">
                    <div class="avatar-overlay">Изменить фото</div>
                    <form id="update-avatar-form" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" class="avatar-input" name="avatar" id="avatar-upload" accept="image/*" onchange="document.getElementById('update-avatar-form').submit();">
                    </form>
                </div>
                <h2 class="profile-name">{{ $user->name }}</h2>
                <div class="profile-status">{{ ucfirst($user->status) ?? 'Пользователь' }}</div>
                
                <p class="profile-join-date">
                    <i class="fas fa-calendar-alt"></i> 
                    На сайте с {{ $user->created_at->format('d.m.Y') }}
                </p>
            </div>
            
            <ul class="profile-menu">
                <li class="profile-menu-item">
                    <a href="#personal" class="profile-menu-link active" data-section="personal-section">
                        <span class="profile-menu-icon"><i class="fas fa-user"></i></span>
                        Личная информация
                    </a>
                </li>
                <li class="profile-menu-item">
                    <a href="#security" class="profile-menu-link" data-section="security-section">
                        <span class="profile-menu-icon"><i class="fas fa-lock"></i></span>
                        Безопасность
                    </a>
                </li>
                <li class="profile-menu-item">
                    <a href="#phone" class="profile-menu-link" data-section="phone-section">
                        <span class="profile-menu-icon"><i class="fas fa-phone"></i></span>
                        Сменить телефон
                    </a>
                </li>
                @if(in_array($user->status, ['partner', 'architect', 'designer', 'executor', 'coordinator']))
                <li class="profile-menu-item">
                    <a href="#rating" class="profile-menu-link" data-section="rating-section">
                        <span class="profile-menu-icon"><i class="fas fa-star"></i></span>
                        Рейтинг и отзывы
                    </a>
                </li>
                @endif
            </ul>
            
            <div class="profile-actions">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-out-alt"></i> Выйти из аккаунта
                </a>
                <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteAccountModal">
                    <i class="fas fa-trash-alt"></i> Удалить аккаунт
                </button>
            </div>
        </div>
        
        <!-- Правая панель профиля -->
        <div class="profile-content">
            <!-- Персональная информация -->
            <div class="profile-card profile-section active" id="personal-section">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Личная информация</h3>
                </div>
                <div class="profile-card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <form id="update-profile-form">
                        @csrf
                        <div class="form-row">
                            <div class="form-column">
                                <div class="form-group-profile">
                                    <label class="form-label" for="name">Имя и фамилия</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" 
                                           placeholder="Введите имя и фамилию" maxlength="100" required>
                                </div>
                            </div>
                            <div class="form-column">
                                <div class="form-group-profile">
                                    <label class="form-label" for="email">Электронная почта</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" 
                                           placeholder="example@domain.com" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Дополнительные поля в зависимости от роли пользователя -->
                        @if($user->status == 'user')
                            <div class="form-group-profile">
                                <label class="form-label" for="city">Город</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}" 
                                       placeholder="Введите название города" maxlength="50">
                            </div>
                        @elseif($user->status == 'partner')
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="city">Город</label>
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}" 
                                               placeholder="Введите название города" maxlength="50">
                                    </div>
                                </div>
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="contract_number">Номер договора</label>
                                        <input type="text" class="form-control" id="contract_number" name="contract_number" 
                                               value="{{ $user->contract_number }}" placeholder="Например: A-12345" maxlength="20" 
                                               pattern="[A-Za-z0-9\-\/]+">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group-profile">
                                <label class="form-label" for="comment">Комментарий</label>
                                <textarea class="form-control form-textarea" id="comment" name="comment" 
                                          placeholder="Введите дополнительную информацию" maxlength="500">{{ $user->comment }}</textarea>
                            </div>
                        @elseif(in_array($user->status, ['executor', 'architect', 'designer']))
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="city">Город/Часовой пояс</label>
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}" 
                                               placeholder="Москва (UTC+3)" maxlength="50">
                                    </div>
                                </div>
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="experience">Стаж работы</label>
                                        <input type="text" class="form-control" id="experience" name="experience" 
                                               value="{{ $user->experience }}" placeholder="Например: 5 лет" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group-profile">
                                <label class="form-label" for="portfolio_link">Ссылка на портфолио</label>
                                <input type="url" class="form-control" id="portfolio_link" name="portfolio_link" 
                                       value="{{ $user->portfolio_link }}" placeholder="https://example.com/portfolio" 
                                       pattern="https?://.+">
                            </div>
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="active_projects_count">Проекты в работе</label>
                                        <input type="number" class="form-control" id="active_projects_count" name="active_projects_count" 
                                               value="{{ $user->active_projects_count }}" min="0" max="100" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        @elseif($user->status == 'coordinator')
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="experience">Стаж работы</label>
                                        <input type="text" class="form-control" id="experience" name="experience" 
                                               value="{{ $user->experience }}" placeholder="Например: 5 лет" maxlength="20">
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="form-footer">
                            <button type="button" class=" btn-secondary">Отменить</button>
                            <button type="submit" class=" btn-primary">Сохранить изменения</button>
                        </div>
                        <div id="profile-update-message" style="display: none;"></div>
                    </form>
                </div>
            </div>
            
            <!-- Безопасность (изменение пароля) -->
            <div class="profile-card profile-section" id="security-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Безопасность</h3>
                </div>
                <div class="profile-card-body">
                    <form id="password-change-form">
                        @csrf
                        <div class="form-group-profile">
                            <label class="form-label" for="new_password">Новый пароль</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="8" maxlength="64" 
                                   placeholder="Минимум 8 символов, включая буквы и цифры" 
                                   pattern="(?=.*\d)(?=.*[a-zA-Z]).{8,}" required>
                          
                        </div>
                        <div class="form-group-profile" style="margin-top: 15px">
                            <label class="form-label" for="new_password_confirmation">Подтверждение пароля</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" 
                                   minlength="8" maxlength="64" 
                                   placeholder="Повторите новый пароль" required>
                        </div>
                        <div class="form-footer">
                            <button type="button" class=" btn-secondary">Отменить</button>
                            <button type="submit" class=" btn-primary">Изменить пароль</button>
                        </div>
                        <div id="password-change-message" style="display: none;"></div>
                    </form>
                </div>
            </div>
            
            <!-- Изменение телефона -->
            <div class="profile-card profile-section" id="phone-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Смена номера телефона</h3>
                </div>
                <div class="profile-card-body">
                    <p>Текущий номер: <strong>{{ $user->phone ?: 'Не указан' }}</strong></p>
                    
                    <form id="phone-change-form">
                        <div class="form-group-profile">
                            <label class="form-label" for="new-phone">Новый номер телефона</label>
                            <input type="text" class="form-control maskphone" id="new-phone" name="new-phone" 
                                   placeholder="+7 (___) ___-__-__" required>
                        </div>
                        <div class="form-footer">
                            <button type="button" class=" btn-secondary">Отменить</button>
                            <button type="button" id="send-code-btn" class=" btn-primary">Отправить код подтверждения</button>
                        </div>
                    </form>
                    
                    <div class="verification-section" id="verification-section">
                        <form id="verification-form">
                            <div class="form-group-profile">
                                <label class="form-label" for="verification-code-1">Введите код подтверждения</label>
                                <div class="verification-code-container">
                                    <input type="text" class="form-control verification-code-input" id="verification-code-1" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                    <input type="text" class="form-control verification-code-input" id="verification-code-2" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                    <input type="text" class="form-control verification-code-input" id="verification-code-3" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                    <input type="text" class="form-control verification-code-input" id="verification-code-4" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                    <input type="hidden" id="verification-code" name="verification-code" required>
                                </div>
                                <p class="help-text">Код отправлен на указанный номер телефона</p>
                            </div>
                            <div class="form-footer">
                                <span class="resend-code" id="resend-code">Отправить код повторно</span>
                                <button type="button" id="verify-code-btn" class=" btn-primary">Подтвердить</button>
                            </div>
                        </form>
                    </div>
                    
                    <div id="phone-change-message" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Рейтинг (для специалистов) -->
            @if(in_array($user->status, ['partner', 'architect', 'designer', 'executor', 'coordinator']))
            <div class="profile-card profile-section" id="rating-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Рейтинг и отзывы</h3>
                </div>
                <div class="profile-card-body">
                    @php
                        $averageRating = isset($user->averageRating) ? $user->averageRating : 0;
                        if (!$averageRating && isset($user->receivedRatings)) {
                            $averageRating = $user->receivedRatings()->avg('score') ?: 0;
                        }
                        $totalRatings = isset($user->receivedRatings) ? $user->receivedRatings()->count() : 0;
                    @endphp
                    
                    <div class="rating-summary">
                        <h4 class="rating-title">Средний рейтинг</h4>
                        <div class="rating-stats-container">
                            <div class="rating-stats-overall">
                                <div class="rating-big-score">{{ number_format($averageRating, 1) }}</div>
                                <div class="rating-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= floor($averageRating))
                                            <i class="fas fa-star"></i>
                                        @elseif ($i - 0.5 <= $averageRating)
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="rating-count">на основе {{ $totalRatings }} {{ trans_choice('оценки|оценок|оценок', $totalRatings) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($totalRatings > 0)
                        <div class="rating-distribution">
                            <h4 class="rating-title">Распределение оценок</h4>
                            <div class="rating-distribution-wrapper">
                                <ul class="rating-bars">
                                    @php
                                        // Собираем статистику по каждой оценке
                                        $ratingStats = [];
                                        $highestCount = 0;
                                        
                                        for($star = 5; $star >= 1; $star--) {
                                            $ratingCount = isset($user->receivedRatings) ? $user->receivedRatings()->where('score', $star)->count() : 0;
                                            $percentage = $totalRatings > 0 ? round(($ratingCount / $totalRatings) * 100) : 0;
                                            $ratingStats[$star] = [
                                                'count' => $ratingCount,
                                                'percentage' => $percentage
                                            ];
                                            
                                            if ($ratingCount > $highestCount) {
                                                $highestCount = $ratingCount;
                                            }
                                        }
                                    @endphp
                                    
                                    @for($star = 5; $star >= 1; $star--)
                                        @php
                                            $ratingCount = $ratingStats[$star]['count'];
                                            $percentage = $ratingStats[$star]['percentage'];
                                            // Определяем класс для подсветки самой частой оценки
                                            $highlightClass = $ratingCount == $highestCount && $ratingCount > 0 ? 'most-common' : '';
                                        @endphp
                                        <li class="rating-bar {{ $highlightClass }}">
                                            <span class="star-label">{{ $star }} <i class="fas fa-star"></i></span>
                                            <div class="progress-container">
                                                <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <div class="rating-details">
                                                <span class="star-count">{{ $ratingCount }}</span>
                                                <span class="star-percent">({{ $percentage }}%)</span>
                                            </div>
                                        </li>
                                    @endfor
                                </ul>
                                
                  
                            </div>
                            
                            @php
                                // Получаем несколько последних отзывов с комментариями
                                $latestReviews = isset($user->receivedRatings) ? 
                                    $user->receivedRatings()
                                        ->whereNotNull('comment')
                                        ->with('raterUser', 'deal')
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get() : 
                                    collect([]);
                            @endphp
                            
                            @if($latestReviews->count() > 0)
                                <h4 class="rating-title mt-4">Последние отзывы о вас</h4>
                                <div class="rating-comments">
                                    @foreach($latestReviews as $review)
                                        <div class="rating-comment">
                                            <div class="comment-header">
                                                <div class="comment-stars">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= $review->score)
                                                            <i class="fas fa-star"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <div class="comment-author">
                                                    {{ $review->raterUser ? $review->raterUser->name : 'Пользователь' }}
                                                </div>
                                                <div class="comment-date">
                                                    {{ $review->created_at->format('d.m.Y') }}
                                                </div>
                                            </div>
                                            <div class="comment-text">
                                                {{ $review->comment }}
                                            </div>
                                            @if($review->deal)
                                                <div class="comment-project">
                                                    Проект: {{ $review->deal->name ?: 'Проект #'.$review->deal->id }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="no-ratings">У вас пока нет оценок от клиентов</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Скрытые формы -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<form id="delete-account-form" action="{{ route('delete_account') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Модальное окно подтверждения удаления аккаунта -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление аккаунта</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить свой аккаунт? Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class=" btn-secondary" data-dismiss="modal">Отменить</button>
                <button type="button" class=" btn-danger" onclick="document.getElementById('delete-account-form').submit();">Удалить аккаунт</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализация скриптов');
    
    // Переключение между разделами профиля
    const menuLinks = document.querySelectorAll('.profile-menu-link');
    console.log('Найдено элементов меню:', menuLinks.length);
    
    const sections = document.querySelectorAll('.profile-section');
    console.log('Найдено секций:', sections.length);
    
    // Назначаем обработчик событий непосредственно на родительский UL элемент для использования делегирования событий
    const menuContainer = document.querySelector('.profile-menu');
    if (menuContainer) {
        console.log('Найден контейнер меню');
        menuContainer.addEventListener('click', function(e) {
            // Находим ближайший элемент .profile-menu-link от места клика
            const link = e.target.closest('.profile-menu-link');
            if (!link) return; // Клик был не по ссылке
            
            e.preventDefault(); // Предотвращаем стандартное поведение
            console.log('Клик по элементу меню:', link.getAttribute('href'));
            
            // Удаляем активный класс у всех ссылок
            menuLinks.forEach(item => {
                item.classList.remove('active');
            });
            
            // Добавляем активный класс текущей ссылке
            link.classList.add('active');
            
            // Скрываем все секции
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Получаем ID секции из атрибута data-section
            const targetSectionId = link.getAttribute('data-section');
            console.log('Целевая секция:', targetSectionId);
            
            // Показываем нужную секцию
            const targetSection = document.getElementById(targetSectionId);
            if (targetSection) {
                console.log('Секция найдена, отображаем');
                targetSection.style.display = 'block';
            } else {
                console.error(`Секция с ID "${targetSectionId}" не найдена на странице`);
            }
        });
    } else {
        console.error('Контейнер меню не найден!');
    }
    
    // Для совместимости также оставляем обработчики на каждой ссылке, но в упрощенном виде
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Прямой клик по ссылке:', link.getAttribute('href'));
            // Обработка выполняется через делегирование выше
        });
    });
    
    // Валидация форм с визуальной обратной связью
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value) {
                input.classList.add('is-invalid');
                isValid = false;
            } else if (input.pattern && input.value && !new RegExp(input.pattern).test(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        // Проверка совпадения паролей
        if (form.id === 'password-change-form') {
            const password = form.querySelector('#new_password');
            const confirmation = form.querySelector('#new_password_confirmation');
            
            if (password.value !== confirmation.value) {
                confirmation.classList.add('is-invalid');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Функция для обновления CSRF токена с обработкой ошибок
    function refreshCsrfToken() {
        return fetch('/refresh-csrf', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json().then(data => {
                    if (data && data.token) {
                        document.querySelector('meta[name="csrf-token"]').content = data.token;
                        return data.token;
                    } else {
                        throw new Error('Получен неверный формат токена');
                    }
                });
            } else {
                // Возвращаем текущий токен при ошибке
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            }
        })
        .catch(error => {
            console.warn('Не удалось обновить CSRF токен:', error);
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        });
    }
    
    // Обработка формы обновления профиля с валидацией
    const updateProfileForm = document.getElementById('update-profile-form');
    if (updateProfileForm) {
        updateProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm(this)) {
                const messageElement = document.getElementById('profile-update-message');
                messageElement.style.display = 'block';
                messageElement.className = 'alert alert-danger';
                messageElement.textContent = 'Пожалуйста, проверьте правильность заполнения всех полей';
                return;
            }
            
            const formData = new FormData(this);
            const urlEncodedData = new URLSearchParams();
            
            // Преобразуем FormData в URLSearchParams
            for (const [name, value] of formData) {
                urlEncodedData.append(name, value);
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                // Отправляем запрос с обновленным токеном
                fetch('{{ route("profile.update_all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: urlEncodedData
                })
                .then(response => {
                    // Пробуем получить JSON ответ независимо от кода ответа
                    return response.text().then(text => {
                        try {
                            // Пытаемся распарсить ответ как JSON
                            const data = JSON.parse(text);
                            if (!response.ok) {
                                if (response.status === 422) {
                                    throw new Error('Ошибка валидации: ' + (data.message || JSON.stringify(data)));
                                }
                                throw new Error('Ошибка сервера: ' + response.status);
                            }
                            return data;
                        } catch (e) {
                            // Если ответ не в формате JSON
                            console.error('Неверный формат ответа:', text);
                            throw new Error('Получен неверный формат ответа');
                        }
                    });
                })
                .then(data => {
                    const messageElement = document.getElementById('profile-update-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        
                        if (data.success) {
                            messageElement.className = 'alert alert-success';
                            messageElement.textContent = data.message || 'Данные успешно обновлены';
                        } else {
                            messageElement.className = 'alert alert-danger';
                            messageElement.textContent = data.message || 'Произошла ошибка при обновлении профиля';
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('profile-update-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = 'Произошла ошибка при обновлении профиля: ' + error.message;
                    }
                });
            });
        });
    }
    
    // Обработка формы смены пароля с валидацией
    const passwordChangeForm = document.getElementById('password-change-form');
    if (passwordChangeForm) {
        passwordChangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm(this)) {
                const messageElement = document.getElementById('password-change-message');
                messageElement.style.display = 'block';
                messageElement.className = 'alert alert-danger';
                messageElement.textContent = 'Пожалуйста, проверьте правильность заполнения полей пароля';
                return;
            }
            
            const formData = new FormData(this);
            const urlEncodedData = new URLSearchParams();
            
            // Преобразуем FormData в URLSearchParams
            for (const [name, value] of formData) {
                urlEncodedData.append(name, value);
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                // Отправляем запрос с обновленным токеном
                fetch('{{ route("profile.change-password") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: urlEncodedData
                })
                .then(response => {
                    // Пробуем получить JSON ответ независимо от кода ответа
                    return response.text().then(text => {
                        try {
                            // Пытаемся распарсить ответ как JSON
                            const data = JSON.parse(text);
                            if (!response.ok) {
                                if (response.status === 422) {
                                    throw new Error('Ошибка валидации: ' + (data.message || JSON.stringify(data)));
                                }
                                throw new Error('Ошибка сервера: ' + response.status);
                            }
                            return data;
                        } catch (e) {
                            // Если ответ не в формате JSON
                            console.error('Неверный формат ответа:', text);
                            throw new Error('Получен неверный формат ответа');
                        }
                    });
                })
                .then(data => {
                    const messageElement = document.getElementById('password-change-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        
                        if (data.success) {
                            messageElement.className = 'alert alert-success';
                            messageElement.textContent = data.message || 'Пароль успешно изменен';
                            passwordChangeForm.reset();
                        } else {
                            messageElement.className = 'alert alert-danger';
                            messageElement.textContent = data.message || 'Произошла ошибка при смене пароля';
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('password-change-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = 'Произошла ошибка при смене пароля: ' + error.message;
                    }
                });
            });
        });
    }
    
    // Обработка отправки кода подтверждения на телефон
    const sendCodeBtn = document.getElementById('send-code-btn');
    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', function() {
            const phone = document.getElementById('new-phone').value;
            if (!phone) {
                alert('Пожалуйста, введите номер телефона');
                return;
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                fetch('{{ route("profile.send-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сервера: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    const messageElement = document.getElementById('phone-change-message');
                    
                    if (data.success) {
                        document.getElementById('verification-section').style.display = 'block';
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-success';
                        messageElement.textContent = 'Код подтверждения отправлен на указанный номер';
                    } else {
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = data.message || 'Произошла ошибка при отправке кода';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('phone-change-message');
                    messageElement.style.display = 'block';
                    messageElement.className = 'alert alert-danger';
                    messageElement.textContent = 'Произошла ошибка при отправке кода: ' + error.message;
                });
            });
        });
    }
    
    // Обработка полей для ввода кода подтверждения
    const setupVerificationCodeInputs = function() {
        const inputs = document.querySelectorAll('.verification-code-input');
        const hiddenInput = document.getElementById('verification-code');
        
        if (!inputs.length || !hiddenInput) {
            console.warn('Элементы для ввода кода не найдены');
            return;
        }
        
        console.log('Инициализация полей для ввода проверочного кода');
        
        const updateHiddenInput = function() {
            const code = Array.from(inputs).map(input => input.value).join('');
            hiddenInput.value = code;
            console.log('Код обновлен:', code);
        };
        
        inputs.forEach((input, index) => {
            // Обработка ввода в поле
            input.addEventListener('input', function(e) {
                // Разрешаем только цифры
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Добавляем класс, если поле заполнено
                if (this.value) {
                    this.classList.add('filled');
                } else {
                    this.classList.remove('filled');
                }
                
                // Обновляем скрытое поле с полным кодом
                updateHiddenInput();
                
                // Если поле заполнено и это не последнее поле, переходим к следующему
                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            
            // Обработка клавиши Backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (!this.value && index > 0) {
                        // Если поле пустое и это не первое поле, переходим к предыдущему
                        inputs[index - 1].focus();
                        // Предотвращаем стандартное поведение Backspace
                        e.preventDefault();
                    } else if (this.value) {
                        // Если поле не пустое, очищаем его
                        this.value = '';
                        this.classList.remove('filled');
                        updateHiddenInput();
                    }
                }
            });
            
            // Обработка вставки из буфера обмена
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                // Получаем текст из буфера обмена
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                // Берем только цифры
                const digits = pasteData.replace(/\D/g, '').slice(0, 4);
                
                if (digits) {
                    // Распределяем цифры по полям ввода
                    for (let i = 0; i < Math.min(digits.length, inputs.length); i++) {
                        inputs[i].value = digits[i];
                        inputs[i].classList.add('filled');
                    }
                    
                    // Устанавливаем фокус на следующее пустое поле или на последнее поле
                    const nextEmpty = Math.min(digits.length, inputs.length - 1);
                    inputs[nextEmpty].focus();
                    
                    // Обновляем скрытое поле
                    updateHiddenInput();
                }
            });
        });
    };
    
    // Инициализируем поля для ввода кода при загрузке DOM
    setupVerificationCodeInputs();
    
    // Обработка подтверждения кода для телефона
    const verifyCodeBtn = document.getElementById('verify-code-btn');
    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function() {
            const phone = document.getElementById('new-phone').value;
            const code = document.getElementById('verification-code').value;
            
            if (!code || code.length !== 4) {
                alert('Пожалуйста, введите полный код подтверждения (4 цифры)');
                return;
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                fetch('{{ route("profile.verify-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        phone: phone,
                        verification_code: code 
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сервера: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    const messageElement = document.getElementById('phone-change-message');
                    messageElement.style.display = 'block';
                    
                    if (data.success) {
                        messageElement.className = 'alert alert-success';
                        messageElement.textContent = data.message || 'Номер телефона успешно обновлен';
                        document.getElementById('verification-section').style.display = 'none';
                        document.getElementById('phone-change-form').reset();
                        
                        // Обновляем отображаемый номер телефона без перезагрузки страницы
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = data.message || 'Неверный или просроченный код';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('phone-change-message');
                    messageElement.style.display = 'block';
                    messageElement.className = 'alert alert-danger';
                    messageElement.textContent = 'Произошла ошибка при проверке кода: ' + error.message;
                });
            });
        });
    }
    
    // Повторная отправка кода
    const resendCodeBtn = document.getElementById('resend-code');
    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            sendCodeBtn.click();
        });
    }
    
    // Улучшенная маска для телефона с автоформатированием
    function maskPhone(event) {
        const blank = "+_ (___) ___-__-__";
        let i = 0;
        const val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        
        this.value = blank.replace(/./g, function (char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        
        if (event.type == "blur" && this.value.length <= 4) {
            this.value = "";
        }
    }
    
    // Маска для ввода телефона
    const phoneInputs = document.querySelectorAll('.maskphone');
    phoneInputs.forEach(input => {
        input.addEventListener('input', maskPhone);
        input.addEventListener('focus', maskPhone);
        input.addEventListener('blur', maskPhone);
    });
    
    // Маска для ввода кода подтверждения (для каждого поля отдельно)
    const codeInputs = document.querySelectorAll('.verification-code-input');
    if (codeInputs.length) {
        codeInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
            });
        });
    }
    
    // Инициализация при загрузке страницы
    // Показываем первую секцию по умолчанию, если не видна
    const defaultSection = document.getElementById('personal-section');
    if (defaultSection && window.getComputedStyle(defaultSection).display === 'none') {
        console.log('Показываем секцию по умолчанию');
        sections.forEach(section => section.style.display = 'none');
        defaultSection.style.display = 'block';
        
        // Активируем соответствующую ссылку
        const defaultLink = document.querySelector('[data-section="personal-section"]');
        if (defaultLink) {
            menuLinks.forEach(link => link.classList.remove('active'));
            defaultLink.classList.add('active');
        }
    }
});
</script>

<style>
.verification-code-container {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    justify-content: center;
}

.verification-code-input {
    width: 50px;
background: #fff
}

.verification-code-input:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.3);
    outline: none;
}

.verification-code-input.filled {
    background-color: #f0f7ff;
}

@media (max-width: 576px) {
    .verification-code-input {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
}
</style>

