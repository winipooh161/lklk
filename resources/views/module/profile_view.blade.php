<div class="profile-container">
    <div class="profile-header">
        <h1 class="profile-title">Профиль пользователя</h1>
        <p class="profile-subtitle">Информация о {{ $target->name }}</p>
    </div>

    <div class="profile-grid">
        <!-- Левая панель профиля -->
        <div class="profile-sidebar">
            <div class="profile-user">
                <div class="profile-avatar-wrapper">
                    <img src="{{ $target->avatar_url ? asset($target->avatar_url) : asset('storage/icon/profile.svg') }}" alt="Аватар" class="profile-avatar">
                </div>
                <h2 class="profile-name">{{ $target->name }}</h2>
                <div class="profile-status">{{ ucfirst($target->status) ?? 'Пользователь' }}</div>
                
                <p class="profile-join-date">
                    <i class="fas fa-calendar-alt"></i> 
                    На сайте с {{ $target->created_at->format('d.m.Y') }}
                </p>
                
                @if(in_array($target->status, ['partner', 'architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                <div class="profile-badges">
                    @if($target->experience)
                    <span class="profile-badge">
                        <i class="fas fa-briefcase"></i> Стаж: {{ $target->experience }}
                    </span>
                    @endif
                    
                    @if($target->rating)
                    <span class="profile-badge">
                        <i class="fas fa-star"></i> Рейтинг: {{ $target->rating }}
                    </span>
                    @endif
                    
                    @if($target->active_projects_count)
                    <span class="profile-badge">
                        <i class="fas fa-tasks"></i> Проекты: {{ $target->active_projects_count }}
                    </span>
                    @endif
                </div>
                @endif
            </div>
            
            <ul class="profile-menu">
                <li class="profile-menu-item">
                    <a href="#info" class="profile-menu-link active" data-section="info-section">
                        <span class="profile-menu-icon"><i class="fas fa-user"></i></span>
                        Информация
                    </a>
                </li>
                @if(in_array($target->status, ['partner', 'architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                <li class="profile-menu-item">
                    <a href="#rating" class="profile-menu-link" data-section="rating-section">
                        <span class="profile-menu-icon"><i class="fas fa-star"></i></span>
                        Рейтинг и отзывы
                    </a>
                </li>
                @endif
            </ul>
            
            <div class="profile-actions">
                @if(in_array($target->status, ['partner', 'architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                    @if($target->portfolio_link)
                    <a href="{{ $target->portfolio_link }}" target="_blank" class="btn btn-primary btn-block">
                        <i class="fas fa-briefcase"></i> Портфолио
                    </a>
                    @endif
                @endif
                <!-- Здесь можно добавить другие действия для взаимодействия с пользователем -->
            </div>
        </div>
        
        <!-- Правая панель профиля -->
        <div class="profile-content">
            <!-- Основная информация -->
            <div class="profile-card profile-section active" id="info-section">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Информация о пользователе</h3>
                </div>
                <div class="profile-card-body">
                    <div class="profile-info-row">
                        <div class="profile-info-label">ФИО</div>
                        <div class="profile-info-value">{{ $target->name }}</div>
                    </div>
                    
                    <div class="profile-info-row">
                        <div class="profile-info-label">Email</div>
                        <div class="profile-info-value">{{ $target->email ?: 'Не указан' }}</div>
                    </div>
                    
                    <div class="profile-info-row">
                        <div class="profile-info-label">Телефон</div>
                        <div class="profile-info-value">
                            @if($target->phone)
                                {{ preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', $target->phone) }}
                            @else
                                Не указан
                            @endif
                        </div>
                    </div>
                    
                    @if($target->city)
                    <div class="profile-info-row">
                        <div class="profile-info-label">Город</div>
                        <div class="profile-info-value">{{ $target->city }}</div>
                    </div>
                    @endif
                    
                    @if($target->status == 'partner' && $target->contract_number)
                    <div class="profile-info-row">
                        <div class="profile-info-label">Номер договора</div>
                        <div class="profile-info-value">{{ $target->contract_number }}</div>
                    </div>
                    @endif
                    
                    @if($target->comment)
                    <div class="profile-info-row">
                        <div class="profile-info-label">Комментарий</div>
                        <div class="profile-info-value">{{ $target->comment }}</div>
                    </div>
                    @endif
                    
                    <!-- Статистика пользователя -->
                    @if(in_array($target->status, ['partner', 'architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                    <div class="profile-stats-container">
                        @if($target->experience)
                        <div class="profile-stat-item">
                            <span class="profile-stat-value">{{ $target->experience }}</span>
                            <span class="profile-stat-label">Опыт работы</span>
                        </div>
                        @endif
                        
                        @if($target->active_projects_count)
                        <div class="profile-stat-item">
                            <span class="profile-stat-value">{{ $target->active_projects_count }}</span>
                            <span class="profile-stat-label">Активных проектов</span>
                        </div>
                        @endif
                        
                        @php
                            // Получаем количество завершенных проектов из свойства или через отношение с моделью Deal
                            if(isset($target->completed_projects_count)) {
                                $completedProjects = $target->completed_projects_count;
                            } elseif(isset($target->dealsPivot)) {
                                $completedProjects = $target->dealsPivot()
                                    ->whereHas('deal', function($q) {
                                        $q->whereIn('status', ['Проект готов', 'Проект завершен']);
                                    })
                                    ->count();
                            } else {
                                $completedProjects = isset($target->completed_projects) ? $target->completed_projects : 0;
                            }
                            
                            // Получаем средний рейтинг из свойства или через отношение с моделью Rating
                            if(isset($target->average_rating)) {
                                $avgRating = $target->average_rating;
                            } elseif(isset($target->receivedRatings)) {
                                $avgRating = $target->receivedRatings()->avg('score') ?: 0;
                            } else {
                                $avgRating = $target->rating ?: 0;
                            }
                        @endphp
                        
                        <div class="profile-stat-item">
                            <span class="profile-stat-value">{{ $completedProjects }}</span>
                            <span class="profile-stat-label">Завершенных проектов</span>
                        </div>
                        
                        <div class="profile-stat-item">
                            <span class="profile-stat-value">{{ number_format((float)$avgRating, 1) }}</span>
                            <span class="profile-stat-label">Средний рейтинг</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Рейтинг (для специалистов) -->
            @if(in_array($target->status, ['partner', 'architect', 'designer', 'executor', 'coordinator', 'visualizer']))
            <div class="profile-card profile-section" id="rating-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Рейтинг и отзывы</h3>
                </div>
                <div class="profile-card-body">
                    @php
                        $averageRating = isset($target->averageRating) ? $target->averageRating : 0;
                        if (!$averageRating && isset($target->receivedRatings)) {
                            $averageRating = $target->receivedRatings()->avg('score') ?: 0;
                        }
                        $totalRatings = isset($target->receivedRatings) ? $target->receivedRatings()->count() : 0;
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
                                            $ratingCount = isset($target->receivedRatings) ? $target->receivedRatings()->where('score', $star)->count() : 0;
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
                                $latestReviews = isset($target->receivedRatings) ? 
                                    $target->receivedRatings()
                                        ->whereNotNull('comment')
                                        ->with('raterUser')
                                        ->orderBy('created_at', 'desc')
                                        ->take(3)
                                        ->get() : 
                                    collect([]);
                            @endphp
                            
                            @if($latestReviews->count() > 0)
                                <h4 class="rating-title mt-4">Последние отзывы</h4>
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
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="no-ratings">У пользователя пока нет оценок</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение между разделами профиля
    const menuLinks = document.querySelectorAll('.profile-menu-link');
    const sections = document.querySelectorAll('.profile-section');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Удаляем активный класс у всех пунктов меню
            menuLinks.forEach(item => {
                item.classList.remove('active');
            });
            
            // Добавляем активный класс текущему пункту
            this.classList.add('active');
            
            // Скрываем все секции
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Показываем нужную секцию
            const targetSection = document.getElementById(this.getAttribute('data-section'));
            targetSection.style.display = 'block';
        });
    });
    
    // Добавляем функцию для форматирования телефона при отображении
    function formatPhoneNumber(phoneNumberString) {
        const cleaned = ('' + phoneNumberString).replace(/\D/g, '');
        if (cleaned.length < 11) return phoneNumberString;
        
        const match = cleaned.match(/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/);
        if (match) {
            return '+' + match[1] + ' (' + match[2] + ') ' + match[3] + '-' + match[4] + '-' + match[5];
        }
        return phoneNumberString;
    }
    
    // Форматируем телефонные номера на странице
    document.querySelectorAll('.profile-info-value').forEach(element => {
        const text = element.textContent.trim();
        if (/^\+?\d{11}$/.test(text.replace(/\D/g, ''))) {
            element.textContent = formatPhoneNumber(text);
        }
    });
});
</script>

