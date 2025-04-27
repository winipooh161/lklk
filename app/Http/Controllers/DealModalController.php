<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFeed;
use App\Models\User;
use App\Models\ChatGroup;
use Illuminate\Support\Facades\Log;

class DealModalController extends Controller
{
    /**
     * Отображение модального окна для сделки.
     */
    public function getDealModal($id)
    {
        try {
            $deal = Deal::with(['coordinator', 'responsibles', 'users'])->findOrFail($id);
            $feeds = DealFeed::where('deal_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            // Получаем групповой чат для сделки, если он существует
            $groupChat = null;
            if ($deal->chat_group_id) {
                $groupChat = ChatGroup::find($deal->chat_group_id);
            }
    
            // Формирование полей сделки (пример для модуля "Заказ")
            $dealFields = $this->getDealFields();

            // Важно: всегда возвращаем JSON с HTML внутри, а не прямой HTML
            return response()->json([
                'success' => true,
                'html' => view('deals.partials.dealModal', compact('deal', 'feeds', 'dealFields', 'groupChat'))->render()
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка отображения модального окна сделки: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Ошибка при загрузке данных сделки: ' . $e->getMessage()], 500);
        }
    }

    private function getDealFields() {
        return [
            'zakaz' => [
                [
                    'name' => 'project_number',
                    'label' => '№ проекта',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 21,
                    'icon' => 'fas fa-hashtag', // Добавлена иконка номера
                ],
                [
                    'name' => 'avatar_path',
                    'label' => 'Аватар сделки',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-image', // Добавлена иконка изображения
                ],
                [
                    'name' => 'package',
                    'label' => 'Пакет',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                    ],
                    'icon' => 'fas fa-box', // Добавлена иконка пакета
                ],
                [
                    'name' => 'status',
                    'label' => 'Статус',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [
                        'Ждем ТЗ' => 'Ждем ТЗ',
                        'Планировка' => 'Планировка',
                        'Коллажи' => 'Коллажи',
                        'Визуализация' => 'Визуализация',
                        'Рабочка/сбор ИП' => 'Рабочка/сбор ИП',
                        'Проект готов' => 'Проект готов',
                        'Проект завершен' => 'Проект завершен',
                        'Проект на паузе' => 'Проект на паузе',
                        'Возврат' => 'Возврат',
                        'Регистрация' => 'Регистрация',
                        'Бриф прикриплен' => 'Бриф прикриплен',
                    ],
                    'icon' => 'fas fa-tag', // Добавлена иконка статуса
                ],
                [
                    'name' => 'price_service_option',
                    'label' => 'Услуга по прайсу',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        'экспресс планировка' => 'Экспресс планировка',
                        'экспресс планировка с коллажами' => 'Экспресс планировка с коллажами',
                        'экспресс проект с электрикой' => 'Экспресс проект с электрикой',
                        'экспресс планировка с электрикой и коллажами' => 'Экспресс планировка с электрикой и коллажами',
                        'экспресс проект с электрикой и визуализацией' => 'Экспресс проект с электрикой и визуализацией',
                        'экспресс рабочий проект' => 'Экспресс рабочий проект',
                        'экспресс эскизный проект с рабочей документацией' => 'Экспресс эскизный проект с рабочей документацией',
                        'экспресс 3Dвизуализация' => 'Экспресс 3Dвизуализация',
                        'экспресс полный дизайн-проект' => 'Экспресс полный дизайн-проект',
                        '360 градусов' => '360 градусов',
                    ],
                    'required' => true,
                    'icon' => 'fas fa-list-check', // Добавлена иконка услуги
                ],
                [
                    'name' => 'rooms_count_pricing',
                    'label' => 'Кол-во комнат по прайсу',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-door-open', // Добавлена иконка комнат
                ],
                [
                    'name' => 'execution_order_comment',
                    'label' => 'Комментарий к заказу',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 1000,
                    'icon' => 'fas fa-comment', // Добавлена иконка комментария
                ],
                [
                    'name' => 'coordinator_id',
                    'label' => 'Координатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'coordinator')->pluck('name', 'id')->toArray(),
                    'icon' => 'fas fa-user-tie', // Добавлена иконка координатора
                ],
                [
                    'name' => 'name',
                    'label' => 'ФИО клиента',
                    'type' => 'text',
                    'id'   => 'nameField',
                    'role' => ['coordinator', 'admin'],
                    'required' => true,
                    'icon' => 'fas fa-user', // Добавлена иконка пользователя
                ],
                [
                    'name' => 'client_phone',
                    'label' => 'Телефон',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'required' => true,
                    'icon' => 'fas fa-phone', // Добавлена иконка телефона
                ],
                [
                    'name' => 'client_city',
                    'label' => 'Город',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [], // Заполняется через AJAX
                    'icon' => 'fas fa-city', // Добавлена иконка города
                ],
                [
                    'name' => 'client_email',
                    'label' => 'Email клиента',
                    'type' => 'email',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-envelope', // Добавлена иконка email
                ],
                [
                    'name' => 'office_partner_id',
                    'label' => 'Партнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'partner')->pluck('name', 'id')->toArray(),
                    'icon' => 'fas fa-handshake', // Добавлена иконка партнера
                ],
                [
                    'name' => 'completion_responsible',
                    'label' => 'Кто делает комплектацию',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [
                        'клиент' => 'Клиент',
                        'партнер' => 'Партнер',
                        'шопинг-лист' => 'Шопинг-лист',
                        'закупки и снабжение от УК' => 'Нужны закупки и снабжение от УК',
                    ],
                    'icon' => 'fas fa-clipboard-check', // Добавлена иконка комплектации
                ],
                [
                    'name' => 'contract_number',
                    'label' => '№ договора',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-file-contract', // Добавлена иконка договора
                ],
                [
                    'name' => 'created_date',
                    'label' => 'Дата создания сделки',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-calendar-plus', // Добавлена иконка даты создания
                ],
                [
                    'name' => 'payment_date',
                    'label' => 'Дата оплаты',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-money-check', // Добавлена иконка даты оплаты
                ],
                [
                    'name' => 'total_sum',
                    'label' => 'Сумма заказа',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.01',
                    'icon' => 'fas fa-ruble-sign', // Добавлена иконка суммы (рубль)
                ],
                [
                    'name' => 'contract_attachment',
                    'label' => 'Приложение',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf,image/jpeg,image/jpg',
                    'icon' => 'fas fa-paperclip', // Добавлена иконка приложения
                ],
                [
                    'name' => 'deal_note',
                    'label' => 'Примечание',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-sticky-note', // Добавлена иконка примечания
                ],
                [
                    'name' => 'measurements_file',
                    'label' => 'Замеры',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => '.pdf,.dwg,image/*',
                    'icon' => 'fas fa-ruler-combined', // Добавлена иконка замеров
                ],
                [
                    'name' => 'measurements_comment',
                    'label' => 'Комментарии по замерам',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-comments', // Добавлена иконка комментариев
                ],
            ],
            'rabota' => [
                [
                    'name' => 'start_date',
                    'label' => 'Дата старта работы по проекту',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-play', // Добавлена иконка старта
                ],
                [
                    'name' => 'project_duration',
                    'label' => 'Общий срок проекта (в рабочих днях)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-hourglass-half', // Добавлена иконка продолжительности
                ],
                [
                    'name' => 'project_end_date',
                    'label' => 'Дата завершения',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-flag-checkered', // Добавлена иконка завершения
                ],
                 [
                    'name' => 'architect_id',
                    'label' => 'Архитектор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'architect')->pluck('name', 'id')->toArray(),
                    'icon' => 'fas fa-drafting-compass', // Добавлена иконка архитектора
                ],
                [
                    'name' => 'plan_final',
                    'label' => 'Планировка финал (PDF, до 20мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-map', // Добавлена иконка планировки
                ],
                [
                    'name' => 'designer_id',
                    'label' => 'Дизайнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'designer')->pluck('name', 'id')->toArray(),
                    'icon' => 'fas fa-palette', // Добавлена иконка дизайнера
                ],
                [
                    'name' => 'final_collage',
                    'label' => 'Коллаж финал (PDF, до 200мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-object-group', // Добавлена иконка коллажа
                ],
                [
                    'name' => 'visualizer_id',
                    'label' => 'Визуализатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'visualizer')->pluck('name', 'id')->toArray(),
                    'icon' => 'fas fa-eye', // Добавлена иконка визуализатора
                ],
                [
                    'name' => 'visualization_link',
                    'label' => 'Ссылка на визуализацию',
                    'type' => 'url',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-link', // Добавлена иконка ссылки
                ],
                [
                    'name' => 'final_project_file',
                    'label' => 'Финал проекта (PDF, до 200мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-file-pdf', // Добавлена иконка PDF файла
                ],
            ],
            'final' => [
                [
                    'name' => 'work_act',
                    'label' => 'Акт выполненных работ (PDF)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-file-signature', // Добавлена иконка акта
                ],
                [
                    'name' => 'chat_screenshot',
                    'label' => 'Скрин чата с оценкой и актом (JPEG)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/jpeg,image/jpg,image/png',
                    'icon' => 'fas fa-camera', // Добавлена иконка скриншота
                ],
                [
                    'name' => 'archicad_file',
                    'label' => 'Исходный файл архикад (pln, dwg)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => '.pln,.dwg',
                    'icon' => 'fas fa-file-code', // Добавлена иконка архикад файла
                ],
                [
                    'name' => 'contract_number',
                    'label' => '№ договора',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-file-contract', // Добавлена иконка договора
                ],
                [
                    'name' => 'created_date',
                    'label' => 'Дата создания сделки',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-calendar-plus', // Добавлена иконка даты создания
                ],
                [
                    'name' => 'payment_date',
                    'label' => 'Дата оплаты',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-money-check', // Добавлена иконка даты оплаты
                ],
                [
                    'name' => 'total_sum',
                    'label' => 'Сумма Заказа',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.01',
                    'icon' => 'fas fa-ruble-sign', // Добавлена иконка суммы (рубль)
                ],
                [
                    'name' => 'contract_attachment',
                    'label' => 'Приложение договора',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf,image/jpeg,image/jpg,image/png',
                    'icon' => 'fas fa-paperclip', // Добавлена иконка приложения
                ],
                [
                    'name' => 'deal_note',
                    'label' => 'Примечание',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-sticky-note', // Добавлена иконка примечания
                ],
            ],
        ];
    }
}