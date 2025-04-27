@if (!empty($title) || !empty($subtitle))
    <div class="form__title" id="top-title">
        <div class="form__title__info">
            @if (!empty($title))
                <h1>{{ $title }}</h1>
            @endif
            @if (!empty($subtitle))
                <p>{{ $subtitle }}</p>
            @endif
        </div>
        {{-- Кнопки навигации --}}
        <div class="form__button flex between">
            <p>Страница {{ $page }}/{{ $totalPages }}</p>
            @if ($page > 1)
                <button type="button" class=" btn-secondary btn-propustit" onclick="goToPrev()">Обратно</button>
            @endif
            <button type="button" class=" btn-primary btn-dalee" onclick="validateAndSubmit()">Далее</button>
            
            @if ($page > 0 && $page < 15)
                <button type="button" class=" btn-warning  btn-propustit" onclick="skipPage()">Пропустить</button>
            @endif
            
            @if ($page >= 15 && !empty(json_decode($brif->skipped_pages ?? '[]')))
                <span class="skipped-notice">Вы заполняете пропущенные страницы</span>
            @endif
        </div>
    </div>
@endif

<form id="briefForm" action="{{ route('common.saveAnswers', ['id' => $brif->id, 'page' => $page]) }}" method="POST"
    enctype="multipart/form-data" class="back__fon__common">
    @csrf
    <!-- Скрытое поле для определения направления перехода -->
    <input type="hidden" name="action" id="actionInput" value="next">
    <!-- Скрытое поле для определения, была ли страница пропущена -->
    <input type="hidden" name="skip_page" id="skipPageInput" value="0">

    <!-- Добавляем стили для ошибок валидации -->
    <style>
        .field-error {
            border: 2px solid #ff0000 !important;
            background-color: #fff0f0 !important;
        }
        
        .error-message {
            color: #ff0000;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        .error-placeholder::placeholder {
            color: #ff0000 !important;
            opacity: 1;
        }
    </style>

    @if($page == 0)
        <div class="form__body flex between wrap pointblock">
            {{-- Используем $questions для вывода чекбоксов комнат --}}
            @foreach($questions as $room)
                <div class="checkpoint flex wrap">
                    <div class="radio">
                        <input type="checkbox" id="room_{{ $room['key'] }}" class="custom-checkbox"
                               name="answers[{{ $room['key'] }}]" value="{{ $room['title'] }}"
                               @if(isset($brif->{$room['key']})) checked @endif>
                        <label for="room_{{ $room['key'] }}">{{ $room['title'] }}</label>
                    </div>
                </div>
            @endforeach
        </div>
        
      
    @endif

    {{-- Блок с вопросами форматов "default" и "faq" --}}
    <div class="form__body flex between wrap">
        @foreach ($questions as $question)
            @if ($question['format'] === 'default')
                <div class="form-group flex wrap">
                    <h2>{{ $question['title'] }}</h2>
                    @if (!empty($question['subtitle']))
                        <p>{{ $question['subtitle'] }}</p>
                    @endif
                    @if ($question['type'] === 'textarea')
                        <textarea name="answers[{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}" 
                            class="form-control required-field {{ $question['key'] == 'question_2_6' ? 'budget-input' : '' }}"
                            data-original-placeholder="{{ $question['placeholder'] }}"
                            maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                    @else
                        <input type="text" name="answers[{{ $question['key'] }}]" class="form-control required-field {{ $question['key'] == 'question_2_6' ? 'budget-input' : '' }}"
                            value="{{ $brif->{$question['key']} ?? '' }}" placeholder="{{ $question['placeholder'] }}"
                            data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                    @endif
                    <span class="error-message">Это поле обязательно для заполнения</span>
                </div>
            @endif

            {{-- Если формат faq — аккордеон --}}
            @if ($question['format'] === 'faq')
                <div class="faq__body">
                    <div class="faq_block flex center">
                        <div class="faq_item">
                            <div class="faq_question" onclick="toggleFaq(this)">
                                <h2>{{ $question['title'] }}</h2>
                                <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    width="24" height="24">
                                    <path d="M7 10l5 5 5-5z"></path>
                                </svg>
                            </div>
                            <div class="faq_answer">
                                @if ($question['type'] === 'textarea')
                                    <textarea name="answers[{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}" 
                                        class="form-control required-field" data-original-placeholder="{{ $question['placeholder'] }}"
                                        maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                                @else
                                    <input type="text" name="answers[{{ $question['key'] }}]" class="form-control required-field"
                                        value="{{ $brif->{$question['key']} ?? '' }}" placeholder="{{ $question['placeholder'] }}"
                                        data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                                @endif
                                <span class="error-message">Это поле обязательно для заполнения</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Если формат checkpoint — чекбоксы --}}
            @if ($question['format'] === 'checkpoint')
                <div class="checkpoint flex wrap">
                    <div class="radio">
                        <input type="checkbox" id="{{ $question['key'] }}" class="custom-checkbox"
                               name="answers[{{ $question['key'] }}]" value="1"
                               @if(isset($brif->{$question['key']}) && $brif->{$question['key']} == 1) checked @endif>
                        <label for="{{ $question['key'] }}">{{ $question['title'] }}</label>
                    </div>
                </div>
            @endif
        @endforeach
        @if ($page == 2)
            <div class="upload__files">
                <h6>Пожалуйста, предоставьте референсы (фото, картинки), которые отражают ваши пожелания по стилю интерьера</h6>
                <div id="drop-zone-references">
                    <p id="drop-zone-references-text">Перетащите файлы сюда или нажмите, чтобы выбрать</p>
                    <input id="referenceInput" type="file" name="references[]" multiple
                        accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png,.heic,.heif">
                </div>
                <p class="error-message" style="color: red;"></p>
                <small>Допустимые форматы: .pdf, .xlsx, .xls, .doc, .docx, .jpg, .jpeg, .png, .heic, .heif</small><br>
                <small>Максимальный суммарный размер: 25 МБ</small>
                @if($brif->references)
                    <div class="uploaded-references">
                        <h6>Загруженные референсы:</h6>
                        <ul>
                            @foreach(json_decode($brif->references, true) ?? [] as $reference)
                                <li>
                                    <a href="{{ asset($reference) }}" target="_blank">{{ basename($reference) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <script>
                const dropZoneReferences = document.getElementById('drop-zone-references');
                const referenceInput = document.getElementById('referenceInput');
                const dropZoneReferencesText = document.getElementById('drop-zone-references-text');
                function updateDropZoneReferencesText() {
                    const files = referenceInput.files;
                    if (files && files.length > 0) {
                        const names = [];
                        for (let i = 0; i < files.length; i++) {
                            names.push(files[i].name);
                        }
                        dropZoneReferencesText.textContent = names.join(', ');
                    } else {
                        dropZoneReferencesText.textContent = "Перетащите файлы сюда или нажмите, чтобы выбрать";
                    }
                }
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZoneReferences.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZoneReferences.addEventListener(eventName, () => {
                        dropZoneReferences.classList.add('dragover');
                    }, false);
                });
                ['dragleave', 'drop'].forEach(eventName => {
                    dropZoneReferences.addEventListener(eventName, () => {
                        dropZoneReferences.classList.remove('dragover');
                    }, false);
                });
                dropZoneReferences.addEventListener('drop', function(e) {
                    let files = e.dataTransfer.files;
                    referenceInput.files = files;
                    updateDropZoneReferencesText();
                });
                referenceInput.addEventListener('change', function() {
                    updateDropZoneReferencesText();
                });
                referenceInput.addEventListener('change', function() {
                    const allowedFormats = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'heic', 'heif'];
                    const errorMessageElement = this.parentElement.nextElementSibling;
                    const files = this.files;
                    let totalSize = 0;
                    errorMessageElement.textContent = '';
                    for (const file of files) {
                        const fileExt = file.name.split('.').pop().toLowerCase();
                        if (!allowedFormats.includes(fileExt)) {
                            errorMessageElement.textContent = `Недопустимый формат файла: ${file.name}.`;
                            this.value = '';
                            return;
                        }
                        totalSize += file.size;
                    }
                    if (totalSize > 25 * 1024 * 1024) {
                        errorMessageElement.textContent = 'Суммарный размер файлов не должен превышать 25 МБ.';
                        this.value = '';
                    }
                });
            </script>
            <style>
             
            </style>
        @endif


    </div>

   
</form>

<!-- JavaScript для проверки заполнения обязательных полей и возможности пропуска страниц -->
<script>
    // Функция для проверки заполнения всех обязательных полей
    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll('.required-field');
        let firstInvalidField = null;
        
        // Сбрасываем стили ошибок для всех полей
        requiredFields.forEach(function(field) {
            field.classList.remove('field-error', 'error-placeholder');
            field.placeholder = field.getAttribute('data-original-placeholder');
            
            const errorMsg = field.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.style.display = 'none';
            }
        });
        
        // Проверяем каждое обязательное поле
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;
                
                // Добавляем стили ошибок
                field.classList.add('field-error', 'error-placeholder');
                field.placeholder = 'Заполните это поле!';
                
                // Показываем сообщение об ошибке
                const errorMsg = field.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.style.display = 'block';
                }
                
                // Сохраняем первое невалидное поле
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
                
                // Если поле в аккордеоне, открываем аккордеон
                const faqItem = field.closest('.faq_item');
                if (faqItem && !faqItem.classList.contains('active')) {
                    toggleFaq(faqItem.querySelector('.faq_question'));
                }
            }
        });
        
        // Если есть невалидное поле, прокручиваем к нему
        if (firstInvalidField) {
            scrollToElement(firstInvalidField);
        }
        
        return isValid;
    }
    
    // Функция для прокрутки к элементу
    function scrollToElement(element) {
        // Получаем позицию элемента относительно документа
        const rect = element.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Вычисляем абсолютную позицию элемента
        const absoluteTop = rect.top + scrollTop;
        
        // Прокручиваем с учетом отступа (для отображения заголовка формы)
        window.scrollTo({
            top: absoluteTop - 120, // 120px - примерная высота шапки
            behavior: 'smooth'
        });
        
        // Добавляем фокус на элемент после прокрутки
        setTimeout(() => {
            element.focus();
            // Добавляем подсвечивание
            element.classList.add('highlight-field');
            // Убираем подсвечивание через 2 секунды
            setTimeout(() => {
                element.classList.remove('highlight-field');
            }, 2000);
        }, 500);
    }
    
    // Функция для отправки формы после валидации
    function validateAndSubmit() {
        if (validateForm()) {
            document.getElementById('actionInput').value = 'next';
            document.getElementById('skipPageInput').value = '0';
            document.getElementById('briefForm').submit();
        }
    }
    
    // Функция для пропуска текущей страницы
    function skipPage() {
        // Проверяем, что страница < 15, так как страницы 15+ нельзя пропускать
        @if ($page < 15)
            // Создаем форму CSRF-токена для отправки
            const csrfToken = '{{ csrf_token() }}';
            
            // Отправляем запрос на пропуск текущей страницы
            fetch('{{ route('common.skipPage', ['id' => $brif->id, 'page' => $page]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin' // Важно для работы с сессиями и куками
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сервера: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Произошла ошибка при пропуске страницы');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при пропуске страницы. Пожалуйста, попробуйте еще раз.');
            });
        @else
            alert('Эту страницу нельзя пропустить.');
        @endif
    }
    
    // Функция для перехода на предыдущую страницу
    function goToPrev() {
        document.getElementById('actionInput').value = 'prev';
        document.getElementById('briefForm').submit();
    }
    
    // Функция для переключения аккордеонов FAQ
    function toggleFaq(questionElement) {
        const faqItem = questionElement.parentElement;
        const faqAnswer = faqItem.querySelector('.faq_answer');
        const inputElement = faqAnswer.querySelector('textarea, input');
        const isActive = faqItem.classList.contains('active');

        if (!isActive) {
            faqItem.classList.add('active');
            faqAnswer.style.height = '0px';
            faqAnswer.offsetHeight; // принудительный reflow
            faqAnswer.style.height = faqAnswer.scrollHeight + 'px';
            if (inputElement) {
                setTimeout(() => {
                    inputElement.focus();
                }, 50);
            }
        } else {
            faqItem.classList.remove('active');
            const currentHeight = faqAnswer.scrollHeight;
            faqAnswer.style.height = currentHeight + 'px';
            faqAnswer.offsetHeight;
            faqAnswer.style.height = '0px';
        }
    }
    
    // Добавляем обработчики событий для полей, чтобы убирать ошибки при вводе
    document.addEventListener('DOMContentLoaded', function() {
        const requiredFields = document.querySelectorAll('.required-field');
        
        requiredFields.forEach(function(field) {
            field.addEventListener('input', function() {
                if (field.value.trim()) {
                    field.classList.remove('field-error', 'error-placeholder');
                    field.placeholder = field.getAttribute('data-original-placeholder');
                    
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.style.display = 'none';
                    }
                }
            });
        });
        
        // Добавляем обработку для полей с классом budget-input
        const budgetInputs = document.querySelectorAll('.budget-input');
        budgetInputs.forEach(function(input) {
            // Инициализация поля при загрузке страницы
            if (input.value && !input.value.endsWith(' руб')) {
                input.value = formatBudgetValue(input.value);
            }
            
            input.addEventListener('input', function(e) {
                // Удаляем все, кроме цифр
                let value = e.target.value.replace(/[^\d]/g, '');
                
                // Форматируем число и добавляем "руб"
                if (value) {
                    e.target.value = formatBudgetValue(value);
                } else {
                    e.target.value = '';
                }
            });
            
            // Перед отправкой формы удаляем "руб" и пробелы
            input.form.addEventListener('submit', function() {
                if (input.value) {
                    // Сохраняем оригинальное значение для отправки
                    const numericValue = input.value.replace(/\s/g, '').replace(/руб/g, '');
                    
                    // Создаем скрытое поле с числовым значением
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    hiddenInput.value = numericValue;
                    
                    // Заменяем оригинальное поле на скрытое
                    input.name = input.name + '_formatted';
                    input.form.appendChild(hiddenInput);
                }
            });
        });
    });
    
    // Функция форматирования бюджетного значения
    function formatBudgetValue(value) {
        // Форматируем число с разделителями тысяч
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' руб';
    }
</script>

<!-- Скрипт для проверки файлов на размер и формат -->
<script>
    document.getElementById('fileInput')?.addEventListener('change', function() {
        const allowedFormats = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'heic', 'heif'];
        const errorMessageElement = document.querySelector('.error-message');
        const files = this.files;
        let totalSize = 0;
        errorMessageElement.textContent = '';
        for (const file of files) {
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!allowedFormats.includes(fileExt)) {
                errorMessageElement.textContent = `Недопустимый формат файла: ${file.name}.`;
                this.value = '';
                return;
            }
            totalSize += file.size;
        }
        if (totalSize > 25 * 1024 * 1024) {
            errorMessageElement.textContent = 'Суммарный размер файлов не должен превышать 25 МБ.';
            this.value = '';
        }
    });
</script>

<style>
    .highlight-field {
        animation: highlightPulse 1s ease-in-out;
        box-shadow: 0 0 10px 2px rgba(255, 0, 0, 0.5);
    }
    
    @keyframes highlightPulse {
        0% { box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5); }
        50% { box-shadow: 0 0 15px 4px rgba(255, 0, 0, 0.8); }
        100% { box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5); }
    }
    
    .skipped-notice {
        color: #ff6600;
        font-weight: bold;
        font-size: 14px;
        padding: 5px 10px;
        background-color: #fff3e0;
        border-radius: 4px;
        border: 1px solid #ff9800;
    }
</style>
