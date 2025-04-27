<div class="modal modal__deal" id="editModal" style="display: none;">
    <div class="modal-content">
        @if(isset($deal) && isset($dealFields))
            <div class="button__points">
                <span class="close-modal" id="closeModalBtn" title="Закрыть окно без сохранения изменений">&times;</span>
                <button data-target="Заказ" class="buttonSealaActive" title="Показать информацию о заказе">Заказ</button>
                <button data-target="Работа над проектом" title="Показать информацию о работе над проектом">Работа над проектом</button>
            @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <button data-target="Финал проекта" title="Показать информацию о финальной стадии проекта">Финал проекта</button>
            @endif
                <ul>
                    <li>
                        <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ $deal->registration_token ? route('register_by_deal', ['token' => $deal->registration_token]) : '#' }}')" title="Скопировать регистрационную ссылку для клиента">
                            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                        </a>
                    </li>
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                        <li>
                            <a href="{{ route('deal.change_logs.deal', ['deal' => $deal->id]) }}" title="Просмотр истории изменений сделки">
                                <img src="/storage/icon/log.svg" alt="Логи">
                            </a>
                        </li>
                    @endif
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                        <li>
                            <a href="{{ $deal->link ? url($deal->link) : '#' }}" title="Открыть бриф клиента">
                                <img src="/storage/icon/brif.svg" alt="Бриф клиента">
                            </a>
                        </li>
                    @endif
                    <li>
                        @php
                            $groupChatUrl = isset($groupChat) && $groupChat ? url('/chats?active_chat=' . $groupChat->id) : '#';
                        @endphp
                        <a href="{{ $groupChatUrl }}" title="Перейти в групповой чат сделки">
                            <img src="/storage/icon/chat.svg" alt="Чат">
                        </a>
                    </li>
                </ul>
            </div>

           
            <!-- Форма редактирования сделки -->
            <form id="editForm" method="POST" enctype="multipart/form-data" action="{{ route('deal.update', $deal->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="deal_id" id="dealIdField" value="{{ $deal->id }}">
                @php
                    $userRole = Auth::user()->status;
                @endphp
                <!-- Модуль: Заказ -->
                <fieldset class="module__deal" id="module-zakaz"style="display: flex;"> 
                    <legend>Заказ</legend>
                    @foreach($dealFields['zakaz'] as $field)
                        <div class="form-group-deal">
                            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                                @if(isset($field['icon']))
                                <i class="{{ $field['icon'] }}"></i>
                                @endif
                                {{ $field['label'] }}:
                                @if($field['name'] == 'client_city')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <select name="{{ $field['name'] }}" id="client_city" class="select2-field">
                                            <option value="">-- Выберите город --</option>
                                            @if(!empty($deal->client_city))
                                                <option value="{{ $deal->client_city }}" selected>{{ $deal->client_city }}</option>
                                            @endif
                                        </select>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    <!-- Для поля координатора - особая обработка -->
                                    @if($field['name'] == 'coordinator_id')
                                        @if(Auth::user()->status == 'partner')
                                            <!-- Партнер может только видеть поле координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(Auth::user()->status == 'coordinator')
                                            <!-- Координатор тоже не может менять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            <!-- Добавляем скрытое поле для сохранения значения -->
                                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <!-- Только администраторы могут изменять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <!-- Стандартное отображение для других полей select -->
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите значение --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'textarea')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @else
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @endif
                                @elseif($field['type'] == 'file')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}">
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @else
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'date')
                                    @if($field['name'] == 'created_date')
                                        @if(in_array($userRole, ['coordinator', 'admin']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <p class="deal-date-display">{{ \Carbon\Carbon::parse($deal->{$field['name']})->format('d.m.Y') }}</p>
                                        @endif
                                    @else
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'number')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                    @else
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
    
                <!-- Модуль: Работа над проектом -->
                <fieldset class="module__deal" id="module-rabota">
                    <legend>Работа над проектом</legend>
                    @foreach($dealFields['rabota'] as $field)
                        <div class="form-group-deal">
                            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                                @if(isset($field['icon']))
                                <i class="{{ $field['icon'] }}"></i>
                                @endif
                                {{ $field['label'] }}:
                                @if($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    <!-- Для поля координатора - особая обработка -->
                                    @if($field['name'] == 'coordinator_id')
                                        @if(Auth::user()->status == 'partner')
                                            <!-- Партнер может только видеть поле координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(Auth::user()->status == 'coordinator')
                                            <!-- Координатор тоже не может менять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            <!-- Добавляем скрытое поле для сохранения значения -->
                                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <!-- Только администраторы могут изменять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <!-- Стандартное отображение для других полей select -->
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите значение --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'textarea')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @else
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @endif
                                @elseif($field['type'] == 'file')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}">
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @else
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'date')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                    @else
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'number')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                    @else
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
    
                <!-- Модуль Финал проекта только для координаторов и администраторов -->
                @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <!-- Модуль: Финал проекта -->
                    <fieldset class="module__deal" id="module-final">
                        <legend>Финал проекта</legend>
                        @foreach($dealFields['final'] as $field)
                            <div class="form-group-deal {{ $field['name'] == 'coordinator_id' ? 'field-coordinator-id' : '' }} {{ $field['name'] == 'office_partner_id' ? 'field-office-partner-id' : '' }}">
                                <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                                    <p>{{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}</p>
                                    @if($field['type'] == 'text')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }} class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'select')
                                        <!-- Для поля координатора - особая обработка -->
                                        @if($field['name'] == 'coordinator_id')
                                            @if(Auth::user()->status == 'partner')
                                                <!-- Партнер может только видеть поле координатора -->
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="read-only-hint">Только для чтения</span>
                                            @elseif(Auth::user()->status == 'coordinator')
                                                <!-- Координатор тоже не может менять координатора -->
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="read-only-hint">Только для чтения</span>
                                                <!-- Добавляем скрытое поле для сохранения значения -->
                                                <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                            @else
                                                <!-- Только администраторы могут изменять координатора -->
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        @else
                                            <!-- Для обычных селектов -->
                                            @if(isset($field['role']) && in_array($userRole, $field['role']))
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                    <option value="">-- Выберите {{ strtolower($field['label'] ?? '') }} --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field">
                                                    <option value="">-- Выберите {{ strtolower($field['label'] ?? '') }} --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="read-only-hint">Только для чтения</span>
                                            @endif
                                        @endif
                                    @elseif($field['type'] == 'textarea')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                        @else
                                            <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'file')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['accept']) ? 'accept='.$field['accept'] : '' }}>
                                            @if($deal->{$field['name']})
                                                <div class="file-link">
                                                    <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотреть текущий файл</a>
                                                </div>
                                            @endif
                                        @else
                                            @if($deal->{$field['name']})
                                                <div class="file-link">
                                                    <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотреть файл</a>
                                                </div>
                                            @else
                                                <input type="text" value="Файл не загружен" disabled class="read-only-field">
                                            @endif
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'date')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        @else
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'number')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['step']) ? 'step='.$field['step'] : '' }}>
                                        @else
                                            <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field" {{ isset($field['step']) ? 'step='.$field['step'] : '' }}>
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'email')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="email" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        @else
                                            <input type="email" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'url')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="url" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        @else
                                            <input type="url" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </fieldset>
                @endif
                <div class="form-buttons">
                    <button type="submit" id="saveButton" title="Сохранить все изменения сделки">Сохранить</button>
                </div>
            </form>
        @endif
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Функция копирования регистрационной ссылки
        window.copyRegistrationLink = function(regUrl) {
            if (regUrl && regUrl !== '#') {
                navigator.clipboard.writeText(regUrl).then(function() {
                    alert('Регистрационная ссылка скопирована: ' + regUrl);
                }).catch(function(err) {
                    console.error('Ошибка копирования ссылки: ', err);
                });
            } else {
                alert('Регистрационная ссылка отсутствует.');
            }
        };
        
        // Загрузка городов из JSON-файла для селекта
        if(document.getElementById('client_city')) {
            // Проверяем наличие jQuery перед использованием
            if (typeof $ === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';
                script.onload = initializeSelect2;
                document.head.appendChild(script);
            } else {
                initializeSelect2();
            }
            
            function initializeSelect2() {
                // Проверяем наличие Select2 перед использованием
                if (typeof $.fn.select2 === 'undefined') {
                    var linkElement = document.createElement('link');
                    linkElement.rel = 'stylesheet';
                    linkElement.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                    document.head.appendChild(linkElement);
                    
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                    script.onload = loadCitiesAndInitSelect2;
                    document.head.appendChild(script);
                } else {
                    loadCitiesAndInitSelect2();
                }
            }
            
            function loadCitiesAndInitSelect2() {
                $.getJSON('/public/cities.json', function(data) {
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

                    // Инициализируем Select2 с полученными данными и настройками доступности
                    $('#client_city').select2({
                        data: select2Data,
                        placeholder: "-- Выберите город --",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editModal'),
                        // Добавляем обработчики для доступности
                        closeOnSelect: true
                    }).on('select2:open', function() {
                        // Исправление проблемы aria-hidden
                        document.querySelector('.select2-container--open').setAttribute('inert', 'false');
                        
                        // Удаляем aria-hidden с dropdown контейнера и родителей
                        setTimeout(function() {
                            var dropdowns = document.querySelectorAll('.select2-dropdown');
                            dropdowns.forEach(function(dropdown) {
                                var parent = dropdown.parentElement;
                                while (parent) {
                                    if (parent.hasAttribute('aria-hidden')) {
                                        parent.removeAttribute('aria-hidden');
                                    }
                                    parent = parent.parentElement;
                                }
                            });
                            
                            // Дополнительно для модального окна
                            if (document.querySelector('.modal[aria-hidden="true"]')) {
                                document.querySelector('.modal[aria-hidden="true"]').removeAttribute('aria-hidden');
                            }
                        }, 10);
                    });
                    
                    // Если город уже был выбран, устанавливаем его
                    var currentCity = "{{ $deal->client_city ?? '' }}";
                    if(currentCity) {
                        // Создаем новый элемент option и добавляем к select
                        if($("#client_city").find("option[value='" + currentCity + "']").length === 0) {
                            var newOption = new Option(currentCity, currentCity, true, true);
                            $('#client_city').append(newOption);
                        }
                        $('#client_city').val(currentCity).trigger('change');
                    }
                })
                .fail(function(jqxhr, textStatus, error) {
                    console.error("Ошибка загрузки JSON файла городов: " + textStatus + ", " + error);
                    // Добавляем резервное решение при ошибке загрузки JSON
                    var currentCity = "{{ $deal->client_city ?? '' }}";                    if(currentCity) {                        var option = new Option(currentCity, currentCity, true, true);                        $('#client_city').append(option).trigger('change');
                    }
                });
            }
        }
        
        // Обработчик закрытия модального окна - убираем возможные остатки aria-hidden
        document.getElementById('closeModalBtn').addEventListener('click', function() {
            // Очистка атрибутов aria-hidden при закрытии
            var modal = document.getElementById('editModal');
            if (modal.hasAttribute('aria-hidden')) {
                modal.removeAttribute('aria-hidden');
            }
        });

        // Отслеживаем изменение статуса сделки на "Проект завершен"
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            const originalStatus = statusSelect.value;
            
            // При отправке формы проверяем, изменился ли статус на "Проект завершен"
            document.getElementById('editForm').addEventListener('submit', function(e) {
                if (statusSelect.value === 'Проект завершен' && originalStatus !== 'Проект завершен') {
                    // Сохраняем ID сделки для последующей проверки оценок
                    localStorage.setItem('completed_deal_id', '{{ $deal->id }}');
                    console.log('[Модальное окно] Сделка переведена в статус "Проект завершен", ID:', '{{ $deal->id }}');
                }
            });
        }

        // При открытии модального окна проверяем, нужно ли показать окно оценки
        const dealId = '{{ $deal->id }}';
        const dealStatus = '{{ $deal->status }}';
        const userStatus = '{{ Auth::user()->status }}';

        // Проверяем завершенные сделки для пользователей, которые должны оценивать
        if (dealStatus === 'Проект завершен' && 
            ['coordinator', 'partner', 'client'].includes(userStatus) &&
            typeof window.checkPendingRatings === 'function') {
            console.log('[Модальное окно] Проверка оценок для завершенной сделки:', dealId);
            setTimeout(() => {
                window.checkPendingRatings(dealId);
            }, 1000);
        }

        // Если есть ID завершенной сделки в localStorage, проверяем оценки
        const completedDealId = localStorage.getItem('completed_deal_id');
        if (completedDealId && 
            ['coordinator', 'partner', 'client'].includes(userStatus) &&
            typeof window.checkPendingRatings === 'function') {
            console.log('[Модальное окно] Проверка оценок для сделки из localStorage:', completedDealId);
            setTimeout(() => {
                window.checkPendingRatings(completedDealId);
                localStorage.removeItem('completed_deal_id');
            }, 1500);
        }

        // Инициализация кастомных файловых инпутов
        function initCustomFileInputs() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                const originalLabel = input.parentNode.querySelector('label');
                const fieldName = input.id;
                const fileType = getFileType(input.accept);
                
                // Создаем новый контейнер для кастомного инпута
                const customContainer = document.createElement('div');
                customContainer.className = `custom-file-upload ${fileType}`;
                customContainer.id = `${fieldName}-container`;
                
                // Создаем лейбл для кнопки выбора файла
                const customLabel = document.createElement('label');
                customLabel.setAttribute('for', fieldName);
                
                // Определяем иконку в зависимости от типа файла
                let fileIcon = 'fa-file-upload';
                if (fileType === 'pdf') fileIcon = 'fa-file-pdf';
                else if (fileType === 'image') fileIcon = 'fa-file-image';
                
                customLabel.innerHTML = `<i class="fas ${fileIcon}"></i> Выбрать файл`;
                
                // Создаем контейнер для превью файла
                const previewContainer = document.createElement('div');
                previewContainer.className = 'file-preview';
                previewContainer.innerHTML = `
                    <i class="fas ${fileIcon}"></i>
                    <span class="file-name"></span>
                    <span class="remove-file"><i class="fas fa-times"></i></span>
                `;
                
                // Заменяем оригинальный контейнер на наш кастомный
                input.parentNode.insertBefore(customContainer, input);
                customContainer.appendChild(input);
                customContainer.appendChild(customLabel);
                customContainer.appendChild(previewContainer);
                
                // Если файл уже был загружен, показываем его в превью
                const fileLink = input.parentNode.querySelector('.file-link');
                if (fileLink) {
                    const fileName = fileLink.querySelector('a').textContent;
                    showFilePreview(input, fileName);
                }
                
                // Добавляем обработчики событий
                input.addEventListener('change', function() {
                    const fileName = this.files.length > 0 ? this.files[0].name : '';
                    showFilePreview(this, fileName);
                });
                
                // Добавляем обработчик для удаления файла
                const removeButton = previewContainer.querySelector('.remove-file');
                removeButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    input.value = '';
                    previewContainer.classList.remove('visible');
                });
            });
        }
        
        // Определяем тип файла по accept атрибуту
        function getFileType(accept) {
            if (!accept) return '';
            if (accept.includes('pdf')) return 'pdf';
            if (accept.includes('image')) return 'image';
            return '';
        }
        
        // Показываем превью выбранного файла
        function showFilePreview(input, fileName) {
            const container = input.closest('.custom-file-upload');
            const preview = container.querySelector('.file-preview');
            const nameElement = preview.querySelector('.file-name');
            
            if (fileName) {
                nameElement.textContent = fileName;
                preview.classList.add('visible');
            } else {
                preview.classList.remove('visible');
            }
        }
        
        // Вызываем инициализацию при загрузке модального окна
        $('#dealModalContainer').on('DOMNodeInserted', '#editModal', function() {
            setTimeout(function() {
                initCustomFileInputs();
            }, 100);
        });

        // Инициализация всплывающих подсказок
        if (typeof $().tooltip === 'function') {
            $('[title]').tooltip({
                placement: 'auto',
                trigger: 'hover',
                delay: {show: 1000, hide: 100}, // Задержка в 1 секунду
                template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
                container: 'body'
            });
        }
    });
</script>

<!-- Подключаем Select2 и добавляем CSS стили для исправления проблемы с aria-hidden -->

<style>
  
</style>
