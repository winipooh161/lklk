<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingViewController extends Controller
{
    /**
     * Конструктор с проверкой доступа
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->status, ['admin', 'coordinator', 'partner'])) {
                abort(403, 'Доступ запрещен. Страница доступна только для администраторов, координаторов и партнеров.');
            }
            return $next($request);
        });
    }

    /**
     * Отображение страницы с рейтингами специалистов
     */
    public function index(Request $request)
    {
        // Параметры фильтрации
        $role = $request->input('role');
        $minRating = $request->input('min_rating');
        $maxRating = $request->input('max_rating');
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'rating_desc'); // По умолчанию сортировка по рейтингу (убыванию)
        // Добавляем параметр для переключения вида (карточки или таблица)
        $viewType = $request->input('view_type', 'blocks');
        
        // Базовый запрос для специалистов, у которых есть рейтинги
        $query = User::whereIn('status', ['architect', 'designer', 'visualizer'])
            ->withCount('receivedRatings')
            ->withAvg('receivedRatings', 'score');
            
        // Применение фильтра по роли
        if ($role && $role !== 'all') {
            $query->where('status', $role);
        }
        
        // Поиск по имени
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Фильтрация по минимальному рейтингу
        if ($minRating) {
            $query->havingRaw('IFNULL(received_ratings_avg_score, 0) >= ?', [$minRating]);
        }
        
        // Фильтрация по максимальному рейтингу
        if ($maxRating) {
            $query->havingRaw('IFNULL(received_ratings_avg_score, 0) <= ?', [$maxRating]);
        }
        
        // Применение сортировки
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'rating_asc':
                $query->orderByRaw('IFNULL(received_ratings_avg_score, 0) ASC');
                break;
            case 'rating_desc':
                $query->orderByRaw('IFNULL(received_ratings_avg_score, 0) DESC');
                break;
            case 'reviews_count_asc':
                $query->orderBy('received_ratings_count', 'asc');
                break;
            case 'reviews_count_desc':
                $query->orderBy('received_ratings_count', 'desc');
                break;
        }
        
        // Получение специалистов с пагинацией
        $specialists = $query->paginate(12)->appends($request->query());
        
        // Получение всех ролей для фильтра
        $roles = [
            'all' => 'Все специалисты',
            'architect' => 'Архитекторы',
            'designer' => 'Дизайнеры',
            'visualizer' => 'Визуализаторы'
        ];
        
        // Получение последних отзывов для каждого специалиста
        foreach ($specialists as $specialist) {
            $specialist->latestRatings = $specialist->receivedRatings()
                ->with('raterUser', 'deal')
                ->latest()
                ->take(3)
                ->get();
        }
        
        // Заголовок страницы
        $title_site = "Рейтинги специалистов | Личный кабинет Экспресс-дизайн";
        
        return view('ratings.specialists', compact(
            'specialists', 
            'roles', 
            'role', 
            'minRating', 
            'maxRating', 
            'search', 
            'sortBy',
            'viewType', // Передаем текущий тип отображения в вид
            'title_site'
        ));
    }
}
