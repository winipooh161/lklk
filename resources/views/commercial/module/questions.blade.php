@if (!empty($title_site) || !empty($description))
    <div class="form__title" id="top-title">
        <div class="form__title__info">
            @if (!empty($title_site))
                <h1>{{ $title_site }}</h1>
            @endif
            @if (!empty($description))
                <p>{{ $description }}</p>
            @endif
        </div>
        {{-- Навигационные кнопки --}}
        <div class="form__button flex between">
            <p>Страница {{ $page }}/{{ $totalPages }}</p>
            @if ($page > 1)
                <button type="button" class=" btn-secondary" id="prevPageButton">Обратно</button>
                <script>
                    document.getElementById('prevPageButton').addEventListener('click', function() {
                        const prevPage = {{ $page }} - 1;
                        if (prevPage >= 1) {
                            window.location.href = '{{ url("commercial/questions/".$brif->id) }}/' + prevPage;
                        }
                    });
                </script>
            @endif
            <button type="button" class=" btn-primary" onclick="goToNext()">Далее</button>
        </div>
    </div>
    <!-- Функции для навигации между шагами (если понадобятся в дальнейшем) -->
    <script>
        function goToNext() {
            // Проверяем валидацию для страниц с обязательными полями (если они есть)
            if ([1, 2, 12].includes({{ $page }})) {
                if (!validateForm()) {
                    return false;
                }
            }
            
            document.getElementById('zone-form').submit();
        }
        
        // Функция для валидации полей
        function validateForm() {
            let isValid = true;
            let firstInvalidField = null;
            
            if ({{ $page }} === 1) {
                // Валидация для страницы 1 (название зон)
                const zoneNameInputs = document.querySelectorAll('input[name^="zones"][name$="[name]"]');
                zoneNameInputs.forEach(function(input) {
                    input.classList.remove('field-error');
                    if (!input.value.trim()) {
                        input.classList.add('field-error');
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    }
                });
            } else if ({{ $page }} === 2) {
                // Валидация для страницы 2 (метраж зон)
                const areaInputs = document.querySelectorAll('input[name^="zones"][name$="[total_area]"], input[name^="zones"][name$="[projected_area]"]');
                areaInputs.forEach(function(input) {
                    input.classList.remove('field-error');
                    if (!input.value.trim()) {
                        input.classList.add('field-error');
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    }
                });
            } else if ({{ $page }} === 12) {
                // Валидация для страницы 12 (бюджет)
                const budgetInputs = document.querySelectorAll('.budget-input');
                let hasValue = false;
                budgetInputs.forEach(function(input) {
                    input.classList.remove('field-error');
                    if (input.value.trim()) {
                        hasValue = true;
                    }
                });
                
                if (!hasValue) {
                    budgetInputs.forEach(function(input) {
                        input.classList.add('field-error');
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    });
                    isValid = false;
                }
            }
            
            // Если есть невалидное поле, прокручиваем к нему
            if (firstInvalidField) {
                scrollToElement(firstInvalidField);
            }
            
            return isValid;
        }
        
        // Функция для прокрутки к элементу
        function scrollToElement(element) {
            const rect = element.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const absoluteTop = rect.top + scrollTop;
            
            window.scrollTo({
                top: absoluteTop - 120,
                behavior: 'smooth'
            });
            
            setTimeout(() => {
                element.focus();
                element.classList.add('highlight-field');
                setTimeout(() => {
                    element.classList.remove('highlight-field');
                }, 2000);
            }, 500);
        }
    </script>
    
    <style>
        .field-error {
            border: 2px solid #ff0000 !important;
            background-color: #fff0f0 !important;
        }
        
        .highlight-field {
            animation: highlightPulse 1s ease-in-out;
            box-shadow: 0 0 10px 2px rgba(255, 0, 0, 0.5);
        }
        
        @keyframes highlightPulse {
            0% { box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5); }
            50% { box-shadow: 0 0 15px 4px rgba(255, 0, 0, 0.8); }
            100% { box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5); }
        }
    </style>
@endif

<form action="{{ route('commercial.saveAnswers', ['id' => $brif->id, 'page' => $page]) }}" method="POST" id="zone-form" enctype="multipart/form-data">
    @csrf

    @if ($page == 2)
        <div id="zones-container">
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h3>{{ $zone['name'] }}</h3>
                    <input maxlength="15" type="text" name="zones[{{ $index }}][total_area]" class="form-control"
                        placeholder="Общая площадь (м²)" value="{{ $zone['total_area'] ?? '' }}" />
                    <input maxlength="15" type="text" name="zones[{{ $index }}][projected_area]"
                        class="form-control" placeholder="Проектная площадь (м²)"
                        value="{{ $zone['projected_area'] ?? '' }}" />
                </div>
            @endforeach
        </div>

    @elseif ($page == 1)
        <div id="zones-container">
            @if (count($zones) > 0)
                @foreach ($zones as $index => $zone)
                    <div class="zone-item">
                        <input type="text" name="zones[{{ $index }}][name]" maxlength="250" value="{{ $zone['name'] ?? '' }}"
                            placeholder="Название зоны" class="form-control" />
                        <textarea maxlength="500" name="zones[{{ $index }}][description]" placeholder="Описание зоны"
                            class="form-control">{{ $zone['description'] ?? '' }}</textarea>
                        <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
                    </div>
                @endforeach
            @else
                <div class="zone-item" id="add-zone">
                    <div class="blur__form__zone">
                        <p>Добавить зону</p>
                    </div>
                </div>
                <div class="zone-item">
                    <div class="zone-item-inputs">
                        <div class="zone-item-inputs-title">
                            <input type="text" name="zones[0][name]" placeholder="Название зоны" maxlength="250" class="form-control" />
                            <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
                        </div>
                        <textarea maxlength="500" name="zones[0][description]" placeholder="Описание зоны" class="form-control"></textarea>
                    </div>
                </div>
            @endif
        </div>

    @elseif ($page == 12)
        <div id="zones-container">
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h3>{{ $zone['name'] }}</h3>
                    <input maxlength="500" type="text" name="budget[{{ $index }}]"
                        class="form-control budget-input" placeholder="Укажите бюджет для {{ $zone['name'] }}"
                        value="{{ $zoneBudgets[$index] ?? '' }}" min="0" step="any"
                        data-zone-index="{{ $index }}" oninput="formatInput(event)" />
                </div>
            @endforeach
            <div class="faq__custom-template__prise">
                <h6>Бюджет: <span id="budget-total">0</span></h6>
                <input type="hidden" id="budget-input" name="price" value="{{ $budget }}">
            </div>
        </div>

    @elseif ($page == 13)
        <div class="upload__files">
            <h6>Загрузите документы (не более 25 МБ суммарно):</h6>
            <div id="drop-zone">
                <p id="drop-zone-text">Перетащите файлы сюда или нажмите, чтобы выбрать</p>
                <input id="fileInput" type="file" name="documents[]" multiple
                    accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png,.heic,.heif">
            </div>
            <p class="error-message" style="color: red;"></p>
            <small>Допустимые форматы: .pdf, .xlsx, .xls, .doc, .docx, .jpg, .jpeg, .png, .heic, .heif</small><br>
            <small>Максимальный суммарный размер: 25 МБ</small>
        </div>
        
        <style>
            .upload__files {
                margin: 20px 0;
                font-family: Arial, sans-serif;
            }
            /* Стилизация области перетаскивания */
            #drop-zone {
                border: 2px dashed #ccc;
                border-radius: 6px;
                padding: 30px;
                text-align: center;
                cursor: pointer;
                position: relative;
                transition: background-color 0.3s ease;
            }
            #drop-zone.dragover {
                background-color: #f0f8ff;
                border-color: #007bff;
            }
            #drop-zone p {
                margin: 0;
                font-size: 16px;
                color: #666;
            }
            /* Скрываем нативное поле выбора файлов */
            #fileInput {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                cursor: pointer;
            }
        </style>
        
        <script>
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('fileInput');
            const dropZoneText = document.getElementById('drop-zone-text');

            function updateDropZoneText() {
                const files = fileInput.files;
                if (files && files.length > 0) {
                    const names = [];
                    for (let i = 0; i < files.length; i++) {
                        names.push(files[i].name);
                    }
                    dropZoneText.textContent = names.join(', ');
                } else {
                    dropZoneText.textContent = "Перетащите файлы сюда или нажмите, чтобы выбрать";
                }
            }

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('dragover');
                }, false);
            });

            dropZone.addEventListener('drop', function(e) {
                let files = e.dataTransfer.files;
                fileInput.files = files;
                updateDropZoneText();
            });

            fileInput.addEventListener('change', function() {
                updateDropZoneText();
            });
        </script>
     
    @else
        <div id="zones-container">
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h3>{{ $zone['name'] }}</h3>
                    <textarea maxlength="500" name="preferences[zone_{{ $index }}][answer]" class="form-control"
                        placeholder="Введите предпочтения для {{ $zone['name'] }}">{{ $preferences['zone_' . $index]['question_' . $page] ?? '' }}</textarea>
                </div>
            @endforeach
        </div>
    @endif

</form>

<script>
    // Добавление новой зоны
    document.getElementById('add-zone')?.addEventListener('click', function() {
        const container = document.getElementById('zones-container');
        const index = container.querySelectorAll('.zone-item').length;
        const zoneHtml = `
            <div class="zone-item">
                <div class="zone-item-inputs">
                    <div class="zone-item-inputs-title">
                        <input type="text" name="zones[${index}][name]" maxlength="250" placeholder="Название зоны" class="form-control" />
                        <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
                    </div>
                    <textarea maxlength="500" name="zones[${index}][description]" placeholder="Описание зоны" class="form-control"></textarea>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', zoneHtml);
    });

    // Удаление зоны
    document.getElementById('zones-container')?.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-zone')) {
            e.target.closest('.zone-item').remove();
        }
    });

    // Форматирование ввода для бюджета
    function formatInput(event) {
        let value = event.target.value;
        value = value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value, 10).toLocaleString('ru-RU');
        }
        event.target.value = value;
    }

    function formatCurrency(amount) {
        const formattedAmount = amount.toLocaleString('ru-RU');
        return formattedAmount + '₽';
    }

    function calculateBudget() {
        let total = 0;
        const budgetInputs = document.querySelectorAll('.zone-item input[name^="budget"]');
        budgetInputs.forEach(function(input) {
            const value = parseFloat(input.value.replace(/\s+/g, '').replace('₽', '')) || 0;
            if (value !== 0) {
                total += value;
            }
        });
        const formattedTotal = formatCurrency(total);
        document.getElementById('budget-total').textContent = formattedTotal;
        document.getElementById('budget-input').value = total;
    }

    document.querySelectorAll('.zone-item input[name^="budget"]').forEach(function(input) {
        input.addEventListener('input', function(event) {
            formatInput(event);
            calculateBudget();
        });
    });

    // Обработка ввода площадей (total_area, projected_area)
    document.addEventListener('DOMContentLoaded', function () {
        const areasInputs = document.querySelectorAll('input[name$="[total_area]"], input[name$="[projected_area]"]');

        areasInputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = value;
            });

            input.addEventListener('blur', function() {
                let value = this.value;
                if (value) {
                    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                }
                this.value = value + ' м²';
            });

            input.addEventListener('focus', function() {
                let value = this.value.replace(' м²', '');
                this.value = value;
            });
        });
    });
</script>

