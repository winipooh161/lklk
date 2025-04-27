<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Portfolio;
use App\Models\RatingRow;
use App\Models\RatingUser;
use App\Models\RatingStar;

class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = Auth::user();
        
        // Проверяем, что пользователь имеет роль архитектора, дизайнера или визуализатора
        if (!in_array($user->status, ['architect', 'designer', 'visualizer'])) {
            return redirect()->route('profile')->with('error', 'Доступ запрещен');
        }
        
        $portfolioItems = $user->portfolioItems()->orderBy('order')->get();
        $title_site = "Управление портфолио | Личный кабинет Экспресс-дизайн";
        
        // Добавляем данные рейтинга из внешней системы
        $ratingData = null;
        
        // Проверяем наличие данных рейтинга через номер телефона
        $ratingUser = null;
        
        if ($user->phone) {
            $normalized = preg_replace('/\D/', '', $user->phone);
            $ratingUser = RatingUser::whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [$normalized])
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, '+7', '8'), ' ', ''), '-', '') = ?", [$normalized])
                ->first();
        }
        
        // Если не нашли по телефону, ищем по имени
        if (!$ratingUser) {
            $ratingUser = RatingUser::where('name', $user->name)->first();
        }
        
        if ($ratingUser) {
            // Получаем данные профиля из rating_rows
            $ratingRow = RatingRow::where('fio', $ratingUser->name)->first();
            
            if ($ratingRow) {
                $stars = $ratingUser->stars;
                $ratingData = [
                    'row' => $ratingRow,
                    'user' => $ratingUser,
                    'stars' => $stars,
                    'averageRating' => $ratingUser->getAverageRating(),
                    'completedProjectsCount' => $ratingUser->getCompletedProjectsCount()
                ];
            }
        }
        
        return view('portfolio', compact('portfolioItems', 'title_site', 'user', 'ratingData'));
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Проверяем, что пользователь имеет роль архитектора, дизайнера или визуализатора
        if (!in_array($user->status, ['architect', 'designer', 'visualizer'])) {
            return redirect()->route('profile')->with('error', 'Доступ запрещен');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $directory = 'portfolio/' . $user->id;
            $path = public_path('storage/' . $directory);
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $image->move($path, $filename);
            $imagePath = $directory . '/' . $filename;
        }
        
        // Получаем максимальный порядок для элементов портфолио пользователя
        $maxOrder = Portfolio::where('user_id', $user->id)->max('order') ?? 0;
        
        Portfolio::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'image_path' => $imagePath,
            'order' => $maxOrder + 1,
        ]);
        
        return redirect()->route('portfolio')->with('success', 'Элемент портфолио успешно добавлен');
    }
    
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $portfolioItem = Portfolio::findOrFail($id);
        
        // Проверяем, что пользователь является владельцем портфолио
        if ($portfolioItem->user_id !== $user->id) {
            return redirect()->route('portfolio')->with('error', 'Доступ запрещен');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        
        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($portfolioItem->image_path) {
                $oldPath = public_path('storage/' . $portfolioItem->image_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Загружаем новое изображение
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $directory = 'portfolio/' . $user->id;
            $path = public_path('storage/' . $directory);
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $image->move($path, $filename);
            $portfolioItem->image_path = $directory . '/' . $filename;
        }
        
        $portfolioItem->title = $request->title;
        $portfolioItem->description = $request->description;
        $portfolioItem->save();
        
        return redirect()->route('portfolio')->with('success', 'Элемент портфолио успешно обновлен');
    }
    
    public function destroy($id)
    {
        $user = Auth::user();
        $portfolioItem = Portfolio::findOrFail($id);
        
        // Проверяем, что пользователь является владельцем портфолио
        if ($portfolioItem->user_id !== $user->id) {
            return redirect()->route('portfolio')->with('error', 'Доступ запрещен');
        }
        
        // Удаляем изображение
        if ($portfolioItem->image_path) {
            $path = public_path('storage/' . $portfolioItem->image_path);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $portfolioItem->delete();
        
        return redirect()->route('portfolio')->with('success', 'Элемент портфолио успешно удален');
    }
    
    public function reorder(Request $request)
    {
        $user = Auth::user();
        $items = $request->input('items', []);
        
        foreach ($items as $index => $itemId) {
            $portfolioItem = Portfolio::find($itemId);
            if ($portfolioItem && $portfolioItem->user_id === $user->id) {
                $portfolioItem->order = $index + 1;
                $portfolioItem->save();
            }
        }
        
        return response()->json(['success' => true]);
    }
}
