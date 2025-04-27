/**
 * Система рейтингов для оценки исполнителей в сделках
 */
(function() {
    // Инициализация при загрузке DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[Рейтинги] Инициализация системы рейтингов');
        
        // Элементы интерфейса
        const ratingModal = document.getElementById('rating-modal');
        if (!ratingModal) {
            console.error('[Рейтинги] Не найден элемент модального окна #rating-modal');
            return;
        }
        
        const stars = ratingModal.querySelectorAll('.rating-stars .star');
        const submitBtn = document.getElementById('submit-rating');
        
        let currentRating = 0;
        let pendingRatings = [];
        let currentRatingIndex = 0;
        let currentDealId = null;
        
        // Проверка необходимости оценок при загрузке
        function checkPendingRatingsOnLoad() {
            const savedState = JSON.parse(localStorage.getItem('pendingRatingsState'));
            if (savedState && savedState.pendingRatings && savedState.pendingRatings.length > 0) {
                console.log('[Рейтинги] Обнаружены сохраненные оценки в localStorage:', savedState);
                pendingRatings = savedState.pendingRatings;
                currentRatingIndex = savedState.currentIndex || 0;
                currentDealId = savedState.dealId;
                showNextRating();
                ratingModal.style.display = 'block';
                blockPageUntilRated();
            }
        }
        
        // Блокировка страницы до завершения оценки
        function blockPageUntilRated() {
            document.body.classList.add('rating-in-progress');
            
            // Блокируем нажатие клавиш Escape и Tab
            document.addEventListener('keydown', preventKeyboardNavigation);
            
            // Предотвращаем закрытие вкладки/браузера
            window.onbeforeunload = function() {
                return "Пожалуйста, оцените всех специалистов перед закрытием страницы.";
            };
        }
        
        // Предотвращение навигации клавиатурой
        function preventKeyboardNavigation(e) {
            if (e.key === 'Escape' || e.key === 'Tab') {
                e.preventDefault();
                // Показываем предупреждение при попытке закрыть модальное окно
                const alert = document.querySelector('.rating-alert');
                if (alert) {
                    alert.style.animation = 'none';
                    setTimeout(() => {
                        alert.style.animation = 'rating-alert-flash 0.5s ease-in-out';
                    }, 10);
                }
            }
        }
        
        // Разблокировка страницы после оценки
        function unblockPage() {
            document.body.classList.remove('rating-in-progress');
            document.removeEventListener('keydown', preventKeyboardNavigation);
            window.onbeforeunload = null;
            localStorage.removeItem('pendingRatingsState');
        }
        
        // Сохранение текущего состояния оценок
        function savePendingRatingsState() {
            localStorage.setItem('pendingRatingsState', JSON.stringify({
                pendingRatings: pendingRatings,
                currentIndex: currentRatingIndex,
                dealId: currentDealId
            }));
            console.log('[Рейтинги] Сохранено состояние оценок в localStorage');
        }
        
        // Инициализация звездочек
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.dataset.value);
                highlightStars(value);
            });
            
            star.addEventListener('mouseout', function() {
                highlightStars(currentRating);
            });
            
            star.addEventListener('click', function() {
                currentRating = parseInt(this.dataset.value);
                highlightStars(currentRating);
            });
        });
        
        // Функция подсветки звезд
        function highlightStars(count) {
            stars.forEach(star => {
                const value = parseInt(star.dataset.value);
                if (value <= count) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        // Обработчик отправки оценки
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                if (currentRating === 0) {
                    const alert = document.querySelector('.rating-alert');
                    alert.textContent = "Пожалуйста, выберите оценку от 1 до 5 звезд!";
                    alert.style.backgroundColor = "#f8d7da";
                    alert.style.color = "#721c24";
                    alert.style.borderColor = "#f5c6cb";
                    alert.style.animation = 'none';
                    setTimeout(() => {
                        alert.style.animation = 'rating-alert-flash 0.5s ease-in-out';
                    }, 10);
                    return;
                }
                
                const userToRate = pendingRatings[currentRatingIndex];
                const comment = document.getElementById('rating-comment').value;
                
                // Получаем CSRF-токен из meta-тега
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Отправка оценки на сервер
                fetch('/ratings/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        deal_id: currentDealId,
                        rated_user_id: userToRate.user_id,
                        score: currentRating,
                        comment: comment,
                        role: userToRate.role
                    })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        // Переход к следующему исполнителю или закрытие модального окна
                        currentRatingIndex++;
                        
                        // Сохраняем текущее состояние
                        savePendingRatingsState();
                        
                        if (currentRatingIndex < pendingRatings.length) {
                            showNextRating();
                        } else {
                            ratingModal.style.display = 'none';
                            resetRatingModal();
                            unblockPage(); // Разблокируем страницу после выставления всех оценок
                            
                            // Показываем уведомление об успешном завершении оценок
                            const successMessage = document.createElement('div');
                            successMessage.className = 'success-message';
                            successMessage.innerHTML = 'Спасибо за оценку всех специалистов!';
                            document.body.appendChild(successMessage);
                            
                            setTimeout(() => {
                                successMessage.style.opacity = '0';
                                setTimeout(() => {
                                    document.body.removeChild(successMessage);
                                }, 500);
                            }, 3000);
                        }
                    } else {
                        alert(response.message || 'Произошла ошибка при сохранении оценки.');
                    }
                })
                .catch(error => {
                    console.error('[Рейтинги] Ошибка при отправке оценки:', error);
                    alert('Произошла ошибка при сохранении оценки.');
                });
            });
        }
        
        // Показ информации о следующем исполнителе для оценки
        function showNextRating() {
            if (currentRatingIndex >= pendingRatings.length) return;
            
            const userToRate = pendingRatings[currentRatingIndex];
            document.getElementById('rating-user-name').textContent = userToRate.name;
            document.getElementById('rating-user-role').textContent = formatRole(userToRate.role);
            document.getElementById('rating-user-avatar').src = userToRate.avatar_url || '/storage/icon/profile.svg';
            document.getElementById('current-rating-index').textContent = currentRatingIndex + 1;
            document.getElementById('total-ratings').textContent = pendingRatings.length;
            
            // Адаптируем заголовок и инструкцию в зависимости от роли оцениваемого
            const modalTitle = document.querySelector('#rating-modal h2');
            const ratingAlert = document.querySelector('.rating-alert');
            
            if (userToRate.role === 'coordinator') {
                modalTitle.textContent = 'Оцените качество планировочных координатора';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество координации проекта от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваша оценка позволит улучшить работу координаторов';
            } else if (userToRate.role === 'architect') {
                modalTitle.textContent = 'Оценка работы архитектора';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество планировочных решений от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваше мнение очень важно для нас и поможет улучшить качество работы архитекторов';
            } else if (userToRate.role === 'designer') {
                modalTitle.textContent = 'Оценка работы дизайнера';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество дизайнерских решений от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваше мнение очень важно для нас и поможет улучшить качество работы дизайнеров';
            } else if (userToRate.role === 'visualizer') {
                modalTitle.textContent = 'Оценка работы визуализатора';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество визуализаций от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваше мнение очень важно для нас и поможет улучшить качество работы визуализаторов';
            } else {
                modalTitle.textContent = 'Оценка работы специалиста';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество работы специалиста от 1 до 5 звезд';
                ratingAlert.textContent = 'Для продолжения работы необходимо оценить всех специалистов по данной сделке';
            }
            
            // Сброс текущей оценки
            currentRating = 0;
            highlightStars(0);
            document.getElementById('rating-comment').value = '';
        }
        
        // Форматирование роли для отображения
        function formatRole(role) {
            const roles = {
                'architect': 'Архитектор',
                'designer': 'Дизайнер',
                'visualizer': 'Визуализатор',
                'coordinator': 'Координатор',
                'partner': 'Партнер'
            };
            return roles[role] || role;
        }
        
        // Сброс модального окна
        function resetRatingModal() {
            currentRating = 0;
            pendingRatings = [];
            currentRatingIndex = 0;
            currentDealId = null;
            highlightStars(0);
            document.getElementById('rating-comment').value = '';
        }
        
        // Проверка наличия Laravel и текущего пользователя
        if (typeof window.Laravel === 'undefined' || !window.Laravel.user) {
            console.error('[Рейтинги] Отсутствует объект window.Laravel или информация о пользователе');
            return;
        }

        // Убеждаемся, что у пользователя есть статус и ID
        if (!window.Laravel.user.status || !window.Laravel.user.id) {
            console.error('[Рейтинги] У пользователя отсутствует статус или ID');
            return;
        }
        
        // Проверяем, может ли текущий пользователь оценивать других
        const userCanRate = ['coordinator', 'partner', 'client'].includes(window.Laravel.user.status);
        console.log('[Рейтинги] Пользователь может оценивать других:', userCanRate, 'Статус:', window.Laravel.user.status);
        
        if (!userCanRate) {
            console.log('[Рейтинги] Пользователь не имеет прав на оценку, пропускаем остальные шаги');
            return;
        }
        
        // Проверка необходимости выставления оценок
        window.checkPendingRatings = function(dealId) {
            console.log('[Рейтинги] Проверка оценок для сделки ID:', dealId);
            
            // Добавляем timestamp для предотвращения кэширования запроса
            const timestamp = new Date().getTime();
            
            // Визуальная обратная связь
            const infoMsg = document.createElement('div');
            infoMsg.className = 'info-message';
            infoMsg.innerHTML = 'Проверка необходимости оценок...';
            document.body.appendChild(infoMsg);
            setTimeout(() => {
                if (document.body.contains(infoMsg)) {
                    document.body.removeChild(infoMsg);
                }
            }, 2000);
            
            // Получаем CSRF-токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/ratings/check-pending?deal_id=${dealId}&t=${timestamp}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache, no-store'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('[Рейтинги] Получен ответ для сделки:', data);
                
                if (data.pending_ratings && data.pending_ratings.length > 0) {
                    console.log('[Рейтинги] Найдено пользователей для оценки:', data.pending_ratings.length);
                    
                    pendingRatings = data.pending_ratings;
                    currentDealId = dealId;
                    currentRatingIndex = 0;
                    
                    // Задержка перед показом модального окна
                    setTimeout(() => {
                        showNextRating();
                        ratingModal.style.display = 'block';
                        blockPageUntilRated();
                        savePendingRatingsState();
                    }, 500);
                } else {
                    console.log('[Рейтинги] Нет пользователей для оценки в сделке', dealId);
                }
            })
            .catch(error => {
                console.error('[Рейтинги] Ошибка при проверке оценок:', error);
                
                // В случае ошибки пробуем запрос еще раз через 2 секунды
                setTimeout(() => {
                    window.checkPendingRatings(dealId);
                }, 2000);
            });
        };

        // Добавляем анимацию мигания для предупреждения
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            @keyframes rating-alert-flash {
                0% { transform: scale(1); }
                50% { transform: scale(1.03); background-color: #ffeeba; }
                100% { transform: scale(1); }
            }
            
            /* Стили для модального окна оценки */
            .rating-in-progress {
                overflow: hidden !important;
            }
            
            .rating-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .rating-modal-content {
                background: #fff;
                border-radius: 10px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }
            
            .rating-user-info {
                display: flex;
                align-items: center;
                margin: 20px 0;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 8px;
            }
            
            .rating-avatar {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                margin-right: 15px;
                object-fit: cover;
            }
            
            .rating-stars {
                display: flex;
                justify-content: center;
                font-size: 30px;
                margin: 20px 0;
            }
            
            .star {
                cursor: pointer;
                color: #ddd;
                margin: 0 5px;
                transition: transform 0.2s;
            }
            
            .star:hover {
                transform: scale(1.2);
            }
            
            .star.active {
                color: #ffbf00;
            }
            
            .rating-comment textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                min-height: 100px;
                margin-top: 10px;
            }
            
            /* Информационные сообщения */
            .info-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: #e9f5ff;
                color: #0069d9;
                border: 1px solid #b8daff;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 9999;
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(styleElement);

        // Проверяем localStorage для поиска ID завершенной сделки
        const completedDealIdFromStorage = localStorage.getItem('completed_deal_id');
        if (completedDealIdFromStorage) {
            console.log('[Рейтинги] Найден ID завершенной сделки в localStorage:', completedDealIdFromStorage);
            setTimeout(() => {
                window.checkPendingRatings(completedDealIdFromStorage);
                localStorage.removeItem('completed_deal_id');
            }, 1500);
        }
        
        // Проверяем наличие уже сохранённых оценок
        checkPendingRatingsOnLoad();

        console.log('[Рейтинги] Скрипт рейтингов успешно инициализирован');
    });
    
    // Функция для непосредственного запуска проверки оценок из других скриптов
    window.runRatingCheck = function(dealId) {
        if (typeof window.checkPendingRatings === 'function') {
            console.log('[Рейтинги] Запуск проверки оценок из внешней функции, ID:', dealId);
            window.checkPendingRatings(dealId);
        } else {
            console.error('[Рейтинги] Функция checkPendingRatings не определена');
            // Пробуем инициализировать через таймаут
            setTimeout(() => {
                if (typeof window.checkPendingRatings === 'function') {
                    window.checkPendingRatings(dealId);
                } else {
                    console.error('[Рейтинги] Функция checkPendingRatings все еще не определена после таймаута');
                }
            }, 2000);
        }
    };
})();
