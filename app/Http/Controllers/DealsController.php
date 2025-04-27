<?php

namespace App\Http\Controllers;

use App\Models\Deal;

use App\Models\User;
use App\Models\DealChangeLog;
use App\Models\DealFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ChatGroup;

class DealsController extends Controller
{
    public function __construct()
    {
        // При необходимости добавьте middleware для аутентификации
    }

    /**
     * Отображение списка сделок.
     * В выборку включаются только те сделки, к которым привязан текущий пользователь.
     */
    public function dealCardinator(Request $request)
    {
        $title_site = "Сделки | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

        $search = $request->input('search');
        $status = $request->input('status');
        $view_type = $request->input('view_type', 'blocks');
        $viewType = $view_type;
        
        // Новые параметры фильтрации
        $package = $request->input('package');
        $priceServiceOption = $request->input('price_service_option');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $partnerId = $request->input('partner_id');
        $sortBy = $request->input('sort_by');

        $query = Deal::query();

        if ($user->status === 'admin') {
            // без фильтра
        } elseif ($user->status === 'partner') {
            $query->where('office_partner_id', $user->id);
        } elseif ($user->status === 'coordinator') {
            $query->where('coordinator_id', $user->id);
        } elseif (in_array($user->status, ['architect', 'designer', 'visualizer'])) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('role', $user->status); // Фильтруем по роли пользователя
            });
        } else {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Применяем базовый поиск
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%")
                  ->orWhere('project_number', 'LIKE', "%{$search}%")
                  ->orWhere('package', 'LIKE', "%{$search}%")
                  ->orWhere('deal_note', 'LIKE', "%{$search}%")
                  ->orWhere('client_city', 'LIKE', "%{$search}%")
                  ->orWhere('total_sum', 'LIKE', "%{$search}%");
            });
        }

        // Применяем фильтр по статусу
        if ($status && $status !== 'null') {
            $query->where('status', $status);
        }
        
        // Применяем новые фильтры
        if ($package) {
            $query->where('package', $package);
        }
        
        if ($priceServiceOption) {
            $query->where('price_service_option', $priceServiceOption);
        }
        
        if ($dateFrom) {
            $query->whereDate('created_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('created_date', '<=', $dateTo);
        }
        
        if ($partnerId) {
            $query->where('office_partner_id', $partnerId);
        }
        
        // Применяем сортировку
        if ($sortBy) {
            switch ($sortBy) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'created_date_asc':
                    $query->orderBy('created_date', 'asc');
                    break;
                case 'created_date_desc':
                    $query->orderBy('created_date', 'desc');
                    break;
                case 'total_sum_asc':
                    $query->orderBy('total_sum', 'asc');
                    break;
                case 'total_sum_desc':
                    $query->orderBy('total_sum', 'desc');
                    break;
            }
        } else {
            // Сортировка по умолчанию
            $query->orderBy('created_at', 'desc');
        }

        $deals = $query->get();

        $deal = null;

        $statuses = [
            'Ждем ТЗ', 'Планировка', 'Коллажи', 'Визуализация', 'Рабочка/сбор ИП',
            'Проект готов', 'Проект завершен', 'Проект на паузе', 'Возврат',
            'В работе', 'Завершенный', 'На потом', 'Регистрация',
            'Бриф прикриплен', 'Поддержка', 'Активный'
        ];

        $feeds = DealFeed::whereIn('deal_id', $deals->pluck('id'))->get();

        return view('cardinators', compact(
            'deals',
            'title_site',
            'search',
            'status',
            'viewType',
            'deal',
            'statuses',
            'feeds'
        ));
    }

 /**
     * Метод для загрузки ленты комментариев по сделке.
     * Вызывается AJAX‑запросом и возвращает JSON с записями ленты.
     */
    public function getDealFeeds($dealId)
    {
        try {
            $deal = Deal::findOrFail($dealId);
            $feeds = $deal->dealFeeds()->with('user')->orderBy('created_at', 'desc')->get();
            $result = $feeds->map(function ($feed) {
                return [
                    'user_name'  => $feed->user->name,
                    'content'    => $feed->content,
                    'date'       => $feed->created_at->format('d.m.Y H:i'),
                    'avatar_url' => $feed->user->avatar_url ? $feed->user->avatar_url : asset('storage/default-avatar.png'),
                ];
            });
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error("Ошибка загрузки ленты: " . $e->getMessage());
            return response()->json(['error' => 'Ошибка загрузки ленты'], 500);
        }
    }

    /**
     * Отображение информации о сделках для клиента.
     */
    public function dealUser()
    {
        $user = Auth::user();
        $userDeals = collect(); // Начинаем с пустой коллекции
        
        try {
            // Для клиента определяем сделки всеми возможными способами
            if ($user->status === 'client' || $user->status === 'user') {
                Log::info('Поиск сделок для клиента', [
                    'user_id' => $user->id, 
                    'phone' => $user->phone, 
                    'status' => $user->status
                ]);
                
                // 1. Получаем сделки по номеру телефона
                $dealsByPhone = Deal::where('client_phone', 'LIKE', '%' . preg_replace('/\D/', '', $user->phone) . '%')->get();
                Log::info('Найдено сделок по телефону: ' . $dealsByPhone->count());
                
                // 2. Получаем сделки по полю user_id
                $dealsByUserId = Deal::where('user_id', $user->id)->get();
                Log::info('Найдено сделок по user_id: ' . $dealsByUserId->count());
                
                // 3. Получаем сделки через связь в таблице deal_user
                $dealsByPivot = $user->deals()->get();
                Log::info('Найдено сделок через pivot-таблицу: ' . $dealsByPivot->count());
                
                // Объединяем все коллекции сделок и удаляем дубликаты
                $userDeals = $dealsByPhone->merge($dealsByUserId)->merge($dealsByPivot)->unique('id');
                Log::info('Общее количество уникальных сделок: ' . $userDeals->count());
            }
            // Для партнера находим сделки, где он указан как office_partner_id
            else if ($user->status === 'partner') {
                $userDeals = Deal::where('office_partner_id', $user->id)->get();
            }
            // Для координатора находим сделки, где он указан как coordinator_id
            else if ($user->status === 'coordinator') {
                $userDeals = Deal::where('coordinator_id', $user->id)->get();
            } 
            else {
                // Для других пользователей - ищем через отношение deals()
                Log::info('Поиск сделок для пользователя с ролью: ' . $user->status);
                $userDeals = $user->deals()->get();
            }
            
            // Загружаем связанных пользователей для всех сделок
            foreach ($userDeals as $deal) {
                $deal->load('users');
            }
            
            // Проверяем наличие завершенных сделок для оценивания (убедимся, что $userDeals - это коллекция)
            $hasCompletedDeals = $userDeals->contains('status', 'Проект завершен');
            
            return view('user', compact('userDeals', 'hasCompletedDeals'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении сделок пользователя: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e
            ]);
            
            return view('user', ['userDeals' => collect(), 'hasCompletedDeals' => false])
                   ->with('error', 'Произошла ошибка при загрузке сделок. Пожалуйста, попробуйте позже.');
        }
    }

    /**
     * Форма создания сделки – доступна для координатора, администратора и партнёра.
     */
    public function createDeal()
    {
        $user = Auth::user();
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        }
        $title_site = "Создание сделки";

        $citiesFile = public_path('cities.json');
        if (file_exists($citiesFile)) {
            $citiesJson = file_get_contents($citiesFile);
            $russianCities = json_decode($citiesJson, true);
        } else {
            $russianCities = [];
        }

        $coordinators = User::where('status', 'coordinator')->get();
        $partners = User::where('status', 'partner')->get();

        return view('create_deal', compact(
            'title_site',
            'user',
            'coordinators',
            'partners',
            'russianCities'
        ));
    }

    /**
     * Сохранение сделки с автоматическим созданием группового чата для ответственных.
     */
    public function storeDeal(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'client_phone'            => 'required|string|max:50',
            'package'                 => 'required|string|max:255',
            'price_service_option'    => 'required|string|max:255',
            'rooms_count_pricing'     => 'nullable|integer|min:1|max:2147483647',
            'execution_order_comment' => 'nullable|string|max:1000',
            'execution_order_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'office_partner_id'       => 'nullable|exists:users,id',
            'coordinator_id'          => 'nullable|exists:users,id',
            'total_sum'               => 'nullable|numeric',
            'measuring_cost'          => 'nullable|numeric',
            'client_info'             => 'nullable|string',
            'payment_date'            => 'nullable|date',
            'execution_comment'       => 'nullable|string',
            'comment'                 => 'nullable|string',
            'client_timezone'         => 'nullable|string',
            'completion_responsible'  => 'required|string', // Изменено с nullable на required
            'start_date'              => 'nullable|date',
            'project_duration'        => 'nullable|integer',
            'project_end_date'        => 'nullable|date',
        ]);

        $user = Auth::user(); 
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        } 
 
        try {
            $coordinatorId = $validated['coordinator_id'] ?? auth()->id();

            // Нормализация номера телефона клиента для поиска (удаление нецифровых символов)
            $normalizedPhone = preg_replace('/\D/', '', $validated['client_phone']);

            // Поиск существующего пользователя по номеру телефона
            $existingUser = User::where('phone', 'LIKE', '%' . $normalizedPhone . '%')->first();
            
            // Используем ID существующего пользователя или текущего авторизованного пользователя
            // Это гарантирует, что user_id никогда не будет NULL
            $userId = $existingUser ? $existingUser->id : auth()->id();

            $deal = Deal::create([
                'name'                   => $validated['name'],
                'client_phone'           => $validated['client_phone'],
                'status'                 => 'Ждем ТЗ', // устанавливаем значение по умолчанию
                'package'                => $validated['package'],
                'client_name'            => $validated['name'],
                'price_service_option'          => $validated['price_service_option'],
                'rooms_count_pricing'    => $validated['rooms_count_pricing'] ?? null,
                'execution_order_comment'=> $validated['execution_order_comment'] ?? null,
                'office_partner_id'      => $validated['office_partner_id'] ?? null,
                'coordinator_id'         => $coordinatorId,
                'total_sum'              => $validated['total_sum'] ?? null,
                'measuring_cost'         => $validated['measuring_cost'] ?? null,
                'client_info'            => $validated['client_info'] ?? null,
                'payment_date'           => $validated['payment_date'] ?? null,
                'execution_comment'      => $validated['execution_comment'] ?? null,
                'comment'                => $validated['comment'] ?? null,
                'client_timezone'        => $validated['client_timezone'] ?? null,
                'completion_responsible' => $validated['completion_responsible'] ?? null,
                'user_id'                => $userId, // Устанавливаем ID найденного пользователя или текущего
                'registration_token'     => Str::random(32),
                'registration_token_expiry' => now()->addDays(7),
                'start_date'             => $validated['start_date'] ?? null,
                'project_duration'       => $validated['project_duration'] ?? null,
                'project_end_date'       => $validated['project_end_date'] ?? null,
            ]);

            // Загрузка файлов
            $fileFields = [
                'avatar',
                'execution_order_file',
            ];

            foreach ($fileFields as $field) {
                $uploadData = $this->handleFileUpload($request, $deal, $field, $field === 'avatar' ? 'avatar_path' : $field);
                if (!empty($uploadData)) {
                    $deal->update($uploadData);
                }
            }

            // Привязываем текущего пользователя как координатора
            $deal->users()->attach([auth()->id() => ['role' => 'coordinator']]);

            // Формируем массив связей для таблицы deal_user
            $dealUsers = [auth()->id() => ['role' => 'coordinator']];
            if ($request->filled('architect_id') && User::where('id', $request->input('architect_id'))->exists()) {
                $dealUsers[$request->input('architect_id')] = ['role' => 'architect'];
                $deal->architect_id = $request->input('architect_id');
            }
            if ($request->filled('designer_id') && User::where('id', $request->input('designer_id'))->exists()) {
                $dealUsers[$request->input('designer_id')] = ['role' => 'designer'];
                $deal->designer_id = $request->input('designer_id');
            }
            if ($request->filled('visualizer_id') && User::where('id', $request->input('visualizer_id'))->exists()) {
                $dealUsers[$request->input('visualizer_id')] = ['role' => 'visualizer'];
                $deal->visualizer_id = $request->input('visualizer_id');
            }

            // Привязываем существующего клиента, если найден
            if ($existingUser) {
                $dealUsers[$existingUser->id] = ['role' => 'client'];
                // Записываем в лог привязку клиента по номеру телефона
                \Illuminate\Support\Facades\Log::info('Клиент привязан к сделке по номеру телефона', [
                    'deal_id' => $deal->id,
                    'client_id' => $existingUser->id,
                    'client_phone' => $validated['client_phone'],
                    'normalized_phone' => $normalizedPhone
                ]);
            }

            $deal->save();
            $deal->users()->attach($dealUsers);

            // Создаем групповой чат для сделки
            if (!empty($dealUsers)) {
                $this->createDealChatGroup($deal, $dealUsers);
            }

            // Отправляем смс с регистрационной ссылкой ТОЛЬКО если клиент ещё не зарегистрирован
            if (!$existingUser) {
                $this->sendSmsNotification($deal, $deal->registration_token);
            } else {
                // Для существующего клиента сразу обновляем статус сделки
                $deal->status = 'Регистрация';
                $deal->save();
            }

            // Добавляем клиента в пользователей сделки, если такого клиента нет по email
            if(!empty($deal->client_email)) {
                $clientByEmail = User::where('email', $deal->client_email)->first();
                if($clientByEmail && !$deal->users()->where('user_id', $clientByEmail->id)->exists()) {
                    $deal->users()->attach($clientByEmail->id, ['role' => 'client']);
                }
            }

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно создана.');
        } catch (\Exception $e) {
            Log::error("Ошибка при создании сделки: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при создании сделки: ' . $e->getMessage());
        }
    }

    /**
     * Создание группового чата для сделки.
     */
    protected function createDealChatGroup($deal, $users)
    {
        try {
            // Создаем новую группу чата
            $chatGroup = ChatGroup::create([
                'name' => 'Сделка #' . $deal->id . (isset($deal->name) ? ' - ' . $deal->name : ''),
                'description' => 'Групповой чат для сделки #' . $deal->id,
                'avatar' => $deal->avatar_path ?? null,
                'created_by' => auth()->id(),
            ]);
            
            // Добавляем всех пользователей в чат
            foreach ($users as $userId => $role) {
                $isAdmin = $role === 'coordinator' || auth()->id() === $userId;
                $chatGroup->users()->attach($userId, [
                    'is_admin' => $isAdmin,
                    'role' => $isAdmin ? 'admin' : 'member'
                ]);
            }
            
            // Обновляем сделку с ID нового чата
            $deal->chat_group_id = $chatGroup->id;
            $deal->save();
            
            return $chatGroup;
        } catch (\Exception $e) {
            Log::error('Ошибка при создании группового чата для сделки: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Обновление сделки с учетом ролей пользователя.
     */
    public function updateDeal(Request $request, $id)
    {
        try {
            $deal = Deal::with(['coordinator', 'responsibles'])->findOrFail($id);
            $original = $deal->getOriginal();
            $user = Auth::user();

            $baseRules = [
                'name'          => 'nullable|string|max:255',
                'client_phone'  => 'nullable|string',
                'client_info'   => 'nullable|string',
                'client_email'  => 'nullable|email',
                'comment'       => 'nullable|string',
                'deal_note'     => 'nullable|string',
                'avatar_path'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
                'created_date'  => 'nullable|date',
                'client_city'   => 'nullable|string', // Добавляем client_city для всех пользователей
            ];

            if (in_array($user->status, ['coordinator', 'admin'])) {
                $baseRules = array_merge($baseRules, [
                    'status'                     => 'nullable|string',
                    'package'                    => 'nullable|string|max:255',
                    'project_number'             => 'nullable|string|max:21',
                    'price_service_option'       => 'nullable|string|max:255',
                    'rooms_count_pricing'        => 'nullable|integer|min:1',
                    'execution_order_comment'    => 'nullable|string|max:1000',
                    'execution_order_file'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                    'office_partner_id'          => 'nullable|exists:users,id',
                    'coordinator_id'             => 'nullable|exists:users,id',
                    'measuring_cost'             => 'nullable|numeric',
                    'payment_date'               => 'nullable|date',
                    'execution_comment'          => 'nullable|string',
                    'office_equipment'           => 'nullable|boolean',
                    'measurement_comments'       => 'nullable|string|max:1000',
                    'measurements_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png,dwg|max:5120',
                    'start_date'                 => 'nullable|date',
                    'project_duration'           => 'nullable|integer',
                    'project_end_date'           => 'nullable|date',
                    'architect_id'               => 'nullable|exists:users,id',
                    'final_floorplan'            => 'nullable|file|mimes:pdf|max:20480',
                    'designer_id'                => 'nullable|exists:users,id',
                    'final_collage'              => 'nullable|file|mimes:pdf|max:204800',
                    'visualizer_id'              => 'nullable|exists:users,id',
                    'visualization_link'         => 'nullable|url',
                    'final_project_file'         => 'nullable|file|mimes:pdf|max:204800',
                    'work_act'                   => 'nullable|file|mimes:pdf|max:10240',
                    'archicad_file'              => 'nullable|file|mimes:pln,dwg|max:307200',
                    'contract_number'            => 'nullable|string|max:100',
                    'contract_attachment'        => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
                    'responsibles'               => 'nullable|array',
                    'responsibles.*'             => 'exists:users,id',
                    'completion_responsible'     => 'nullable|string',
                    'client_city'                => 'nullable|string',
                ]);
            } else {
                $baseRules = array_merge($baseRules, [
                    'total_sum' => 'nullable|numeric',
                ]);
            }

            $validated = $request->validate($baseRules);
            $updateData = $validated;

            $statusChanged = false;
            if ($request->has('status') && $request->input('status') !== $original['status']) {
                if ($request->input('status') === 'Проект завершен') {
                    $statusChanged = true;
                }
            }

            // Сохраняем предыдущего координатора перед обновлением
            $oldCoordinatorId = $deal->coordinator_id;

            // Защита от изменения координатора не-администратором
            if ($user->status !== 'admin' && isset($updateData['coordinator_id']) && $updateData['coordinator_id'] != $oldCoordinatorId) {
                // Удаляем поле coordinator_id из данных обновления, если пользователь не админ
                unset($updateData['coordinator_id']);
                Log::warning('Попытка изменения координатора пользователем без прав', [
                    'user_id' => $user->id, 
                    'user_status' => $user->status,
                    'deal_id' => $deal->id
                ]);
            }

            // Сохраняем старый статус для сравнения
            $oldStatus = $deal->status;

            $deal->update($updateData);

            $this->logDealChanges($deal, $original, $deal->getAttributes());

            $fileFields = ($user->status === 'partner')
                ? ['avatar_path']
                : [
                    'avatar_path',
                    'execution_order_file',
                    'measurements_file',
                    'final_floorplan',
                    'final_collage',
                    'final_project_file',
                    'work_act',
                    'archicad_file',
                    'contract_attachment',
                ];

            foreach ($fileFields as $field) {
                $uploadData = $this->handleFileUpload($request, $deal, $field, $field);
                if (!empty($uploadData)) {
                    $deal->update($uploadData);
                }
            }

            // Обновляем связи в deal_user:
            $dealUsers = [];
            
            // Всегда добавляем текущего пользователя, который выполняет обновление
            $dealUsers[Auth::id()] = ['role' => $user->status];
            
            // Проверяем, изменился ли координатор и это изменение от администратора
            if ($request->filled('coordinator_id') && $oldCoordinatorId != $request->input('coordinator_id') && $user->status == 'admin') {
                // Сохраняем старого координатора как участника сделки с ролью coordinator
                if ($oldCoordinatorId) {
                    $dealUsers[$oldCoordinatorId] = ['role' => 'coordinator'];
                    
                    // Логируем сохранение предыдущего координатора
                    Log::info('Сохранение предыдущего координатора при смене', [
                        'deal_id' => $deal->id,
                        'old_coordinator_id' => $oldCoordinatorId,
                        'new_coordinator_id' => $request->input('coordinator_id')
                    ]);
                }
                
                // Добавляем нового координатора
                $dealUsers[$request->input('coordinator_id')] = ['role' => 'coordinator'];
                
                // Если запрос без AJAX, добавляем flash-сообщение о смене координатора
                if (!$request->ajax()) {
                    session()->flash('info', 'Координатор сделки изменен. Предыдущий координатор сохраняет доступ.');
                }
            } else {
                // Если координатор не менялся или изменение не от админа, добавляем текущего координатора
                if ($deal->coordinator_id) {
                    $dealUsers[$deal->coordinator_id] = ['role' => 'coordinator'];
                }
            }

            // Добавляем остальных пользователей сделки
            if ($request->filled('architect_id') && User::where('id', $request->input('architect_id'))->exists()) {
                $dealUsers[$request->input('architect_id')] = ['role' => 'architect'];
                $deal->architect_id = $request->input('architect_id');
            }
            if ($request->filled('designer_id') && User::where('id', $request->input('designer_id'))->exists()) {
                $dealUsers[$request->input('designer_id')] = ['role' => 'designer'];
                $deal->designer_id = $request->input('designer_id');
            }
            if ($request->filled('visualizer_id') && User::where('id', $request->input('visualizer_id'))->exists()) {
                $dealUsers[$request->input('visualizer_id')] = ['role' => 'visualizer'];
                $deal->visualizer_id = $request->input('visualizer_id');
            }
            
            // Добавляем клиента и партнера в сделку
            if(!empty($deal->client_email)) {
                $client = User::where('email', $deal->client_email)->first();
                if($client) {
                    $dealUsers[$client->id] = ['role' => 'client'];
                }
            }
            
            // Убедимся, что партнер добавлен к пользователям сделки
            if($deal->office_partner_id) {
                $partner = User::find($deal->office_partner_id);
                if($partner) {
                    $dealUsers[$partner->id] = ['role' => 'partner'];
                }
            }
            
            if ($request->has('responsibles') && in_array($user->status, ['coordinator', 'admin'])) {
                $responsibles = collect($request->input('responsibles'))
                    ->map(function($id) { return ['role' => 'responsible']; })->toArray();
                $validResponsibles = User::whereIn('id', array_keys($responsibles))->pluck('id')->toArray();
                $responsibles = array_intersect_key($responsibles, array_flip($validResponsibles));
                $dealUsers = array_merge($dealUsers, $responsibles);
            }

            // При смене статуса на "Проект завершен"
            if ($statusChanged) {
                Log::info('Сделка переведена в статус "Проект завершен"', [
                    'deal_id' => $deal->id,
                    'user_id' => Auth::id()
                ]);
                
                // Гарантируем, что партнер добавлен в dealUsers с правильной ролью
                if ($deal->office_partner_id) {
                    $partner = User::find($deal->office_partner_id);
                    if ($partner) {
                        $dealUsers[$deal->office_partner_id] = ['role' => 'partner'];
                        Log::info('Партнер добавлен к сделке для оценки', [
                            'deal_id' => $deal->id,
                            'user_id' => $deal->office_partner_id,
                            'status' => 'partner'
                        ]);
                    }
                }
                
                // Убедимся, что все активные исполнители добавлены к сделке
                $executors = User::whereIn('id', [$deal->architect_id, $deal->designer_id, $deal->visualizer_id])
                                ->whereNotNull('id')
                                ->get();
                
                foreach ($executors as $executor) {
                    if (!isset($dealUsers[$executor->id])) {
                        $dealUsers[$executor->id] = ['role' => $executor->status];
                    }
                }
                
                // Убедимся, что координатор добавлен для оценки
                if ($deal->coordinator_id && !isset($dealUsers[$deal->coordinator_id])) {
                    $dealUsers[$deal->coordinator_id] = ['role' => 'coordinator'];
                }
                
                // Добавляем клиента, если есть контактный email
                if (!empty($deal->client_email)) {
                    $client = User::where('email', $deal->client_email)->first();
                    if ($client && !isset($dealUsers[$client->id])) {
                        $dealUsers[$client->id] = ['role' => 'client'];
                    }
                }
                
                // Логируем итоговый список пользователей для отладки
                Log::info('Итоговый список пользователей сделки', [
                    'deal_id' => $deal->id,
                    'users' => $dealUsers
                ]);
            }

            // Важно: используем attach вместо sync, чтобы не удалять существующие связи
            // и затем обновляем роли для тех, кто есть в $dealUsers
            foreach ($dealUsers as $userId => $attributes) {
                if (!$deal->users()->where('user_id', $userId)->exists()) {
                    $deal->users()->attach($userId, $attributes);
                } else {
                    $deal->users()->updateExistingPivot($userId, $attributes);
                }
            }

            // Обновляем групповой чат, если изменились ответственные за сделку
            if ($deal->chat_group_id) {
                $chatGroup = ChatGroup::find($deal->chat_group_id);
                if ($chatGroup) {
                    // Синхронизируем пользователей в чате с теми, кто привязан к сделке
                    $chatUsers = collect($dealUsers)->map(function($role, $userId) {
                        return ['user_id' => $userId, 'is_admin' => $role === 'coordinator', 'role' => $role === 'coordinator' ? 'admin' : 'member'];
                    })->keyBy('user_id')->toArray();
                    
                    $chatGroup->users()->sync($chatUsers);
                    
                    // Обновляем имя группы, если изменилось имя сделки
                    if ($request->has('name') && $request->input('name') !== $deal->getOriginal('name')) {
                        $chatGroup->name = 'Сделка #' . $deal->id . ' - ' . $request->input('name');
                        $chatGroup->save();
                    }
                }
            } else {
                // Создаем групповой чат, если его еще нет
                $this->createDealChatGroup($deal, $dealUsers);
            }

            // После обновления сделки проверяем изменение статуса
            if (isset($updateData['status']) && $oldStatus !== $updateData['status']) {
                $this->notifyCoordinatorAboutStatusChange($deal, $oldStatus);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'deal' => $deal,
                    'status_changed_to_completed' => $statusChanged
                ]);
            }

            if ($statusChanged) {
                // Хранение ID сделки в сессии для последующей проверки оценок
                session(['completed_deal_id' => $deal->id]);
                
                return redirect()
                    ->route('deal.cardinator')
                    ->with('success', 'Сделка успешно обновлена. Не забудьте оценить работу специалистов!')
                    ->with('completed_deal_id', $deal->id);
            }

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно обновлена.');
        } catch (\Exception $e) {
            Log::error("Ошибка при обновлении сделки: " . $e->getMessage(), ['exception' => $e]);
            if ($request->ajax()) {
                return response()->json(['error' => 'Ошибка при обновлении сделки.'], 500);
            }
            return redirect()->back()->with('error', 'Ошибка при обновлении сделки.');
        }
    }

    /**
     * Отправляет SMS-уведомление координатору о смене статуса сделки
     *
     * @param \App\Models\Deal $deal Сделка с обновленным статусом
     * @param string $oldStatus Предыдущий статус сделки
     * @return void
     */
    protected function notifyCoordinatorAboutStatusChange($deal, $oldStatus)
    {
        try {
            // Проверяем наличие координатора
            if (!$deal->coordinator_id) {
                Log::warning("Не удалось отправить SMS: у сделки #{$deal->id} не указан координатор");
                return;
            }
            
            // Получаем данные координатора
            $coordinator = \App\Models\User::find($deal->coordinator_id);
            if (!$coordinator || !$coordinator->phone) {
                Log::warning("Не удалось отправить SMS: у координатора сделки #{$deal->id} нет номера телефона");
                return;
            }
            
            // Формируем сообщение
            $message = "Статус сделки #{$deal->id} изменен c \"{$oldStatus}\" на \"{$deal->status}\". Клиент: {$deal->name}";
            
            // Ограничиваем длину сообщения
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }
            
            // Отправляем SMS через сервис
            $smsService = new \App\Services\SmsService();
            $result = $smsService->sendSms($coordinator->phone, $message);
            
            if (!$result) {
                Log::error("Ошибка при отправке SMS координатору {$coordinator->name} ({$coordinator->phone})");
            }
        } catch (\Exception $e) {
            Log::error("Исключение при отправке SMS о смене статуса: " . $e->getMessage());
        }
    }

    protected function logDealChanges($deal, $original, $new)
    {
        foreach (['updated_at', 'created_at'] as $key) {
            unset($original[$key], $new[$key]);
        }

        $changes = [];
        foreach ($new as $key => $newValue) {
            if (array_key_exists($key, $original) && $original[$key] != $newValue) {
                $changes[$key] = [
                    'old' => $original[$key],
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($changes)) {
            DealChangeLog::create([
                'deal_id'   => $deal->id,
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'changes'   => $changes,
            ]);
        }
    }

    /**
     * Отправка SMS-уведомления с регистрационной ссылкой.
     */
    private function sendSmsNotification($deal, $registrationToken)
    {
        if (!$registrationToken) {
            Log::error("Отсутствует регистрационный токен для сделки ID: {$deal->id}");
            throw new \Exception('Отсутствует регистрационный токен для сделки.');
        }

        $rawPhone = preg_replace('/\D/', '', $deal->client_phone);
        $registrationLinkUrl = route('register_by_deal', ['token' => $registrationToken]);
        $apiKey = '6CDCE0B0-6091-278C-5145-360657FF0F9B';

        $response = Http::get("https://sms.ru/sms/send", [
            'api_id'    => $apiKey,
            'to'        => $rawPhone,
            'msg'       => "Здравствуйте! Для регистрации пройдите по ссылке: $registrationLinkUrl",
            'partner_id'=> 1,
        ]);

        if ($response->failed()) {
            Log::error("Ошибка при отправке SMS для сделки ID: {$deal->id}. Ответ сервера: " . $response->body());
            throw new \Exception('Ошибка при отправке SMS.');
        }
    }

    /**
     * Обработка загрузки файлов.
     */
    private function handleFileUpload(Request $request, $deal, $field, $targetField = null)
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            // изменено: обрабатываем и "avatar", и "avatar_path" как аватар сделки
            if ($field === 'avatar' || $field === 'avatar_path') {
                $dir = "dels/{$deal->id}"; // изменено: файл сохраняется в папку dels/{id сделки}
                $fileName = "avatar." . $request->file($field)->getClientOriginalExtension(); // имя файла всегда "avatar"
            } else {
                $dir = "dels/{$deal->id}";
                $fileName = $field . '.' . $request->file($field)->getClientOriginalExtension();
            }
            $filePath = $request->file($field)->storeAs($dir, $fileName, 'public');
            return [$targetField ?? $field => $filePath]; // для аватара "avatar_path" будет установлен путь сохранённого файла
        }
        return [];
    }

    public function storeDealFeed(Request $request, $dealId)
    {
        $request->validate([
            'content' => 'required|string|max:1990',
        ]);

        $deal = Deal::findOrFail($dealId);
        $user = Auth::user();

        $feed = new DealFeed();
        $feed->deal_id = $deal->id;
        $feed->user_id = $user->id;
        $feed->content = $request->input('content');
        $feed->save();

        return response()->json([
            'user_name'  => $user->name,
            'content'    => $feed->content,
            'date'       => $feed->created_at->format('d.m.Y H:i'),
            'avatar_url' => $user->avatar_url,
        ]);
    }

    /**
     * Отображение логов изменений для конкретной сделки.
     */
    public function changeLogsForDeal($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        $logs = DealChangeLog::where('deal_id', $deal->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $title_site = "Логи изменений сделки";
        return view('deal_change_logs', compact('deal', 'logs', 'title_site'));
    }
}
