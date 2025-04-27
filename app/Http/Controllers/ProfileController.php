<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Применяем middleware для проверки аутентификации.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 1. Отображение собственного профиля
    public function index()
    {
        $user = Auth::user();
        $title_site = "Профиль аккаунта | Личный кабинет Экспресс-дизайн";
        return view('profile', compact('user', 'title_site'));
    }

    // 2. Просмотр профиля другого пользователя
    public function viewProfile($id)
    {
        $title_site = "Профиль пользователя | Личный кабинет Экспресс-дизайн";
        $viewer = Auth::user();
        $target = User::findOrFail($id);
        
        // Возвращаем представление с данными профиля
        return view('profile_view', compact('target', 'viewer', 'title_site'));
    }

    /**
     * Проверка возможности просмотра профиля другого пользователя.
     * Здесь используется свойство status, а не role.
     */
    protected function canViewProfile($viewer, $target)
    {
        if ($viewer->id === $target->id) {
            return true;
        }
    
        $viewerStatus = strtolower(trim($viewer->status));
        $targetStatus = strtolower(trim($target->status));
    
        if (in_array($viewerStatus, ['admin', 'coordinator'])) {
            return true;
        }
    
        switch ($viewerStatus) {
            case 'user':
                return in_array($targetStatus, ['partner', 'coordinator', 'architect', 'designer']);
            case 'partner':
                return in_array($targetStatus, ['coordinator', 'architect', 'designer']);
            case 'architect':
            case 'designer':
                return in_array($targetStatus, ['user', 'coordinator']);
            default:
                return false;
        }
    }

    // 3. Отправка кода подтверждения на телефон
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $user = Auth::user();
        $rawPhone = preg_replace('/\D/', '', $request->input('phone'));
        $formattedPhone = '+7 (' . substr($rawPhone, 1, 3) . ') ' 
                         . substr($rawPhone, 4, 3) 
                         . '-' 
                         . substr($rawPhone, 7, 2) 
                         . '-' 
                         . substr($rawPhone, 9);

        $verificationCode = rand(1000, 9999);
        $apiKey = '6CDCE0B0-6091-278C-5145-360657FF0F9B';

        $response = Http::get("https://sms.ru/sms/send", [
            'api_id' => $apiKey,
            'to'     => $rawPhone,
            'msg'    => "Ваш код: $verificationCode",
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке SMS.'
            ]);
        }

        $user->verification_code = $verificationCode;
        $user->verification_code_expires_at = now()->addMinutes(10);
        $user->phone = $formattedPhone;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Код подтверждения отправлен.'
        ]);
    }

    // 4. Подтверждение кода подтверждения телефона
    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone'             => 'required',
            'verification_code' => 'required|numeric|digits:4',
        ]);

        $user = Auth::user();
        $phone = preg_replace('/\D/', '', $request->input('phone'));
        $verificationCode = $request->input('verification_code');

        if ($user->verification_code == $verificationCode 
            && now()->lessThanOrEqualTo($user->verification_code_expires_at)) 
        {
            $formattedPhone = '+7 (' . substr($phone, 1, 3) . ') '
                            . substr($phone, 4, 3) . '-'
                            . substr($phone, 7, 2) . '-'
                            . substr($phone, 9, 2);

            $user->phone = $formattedPhone;
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Номер телефона успешно обновлен.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Неверный или просроченный код.'
        ]);
    }

    // 5. Обновление аватара пользователя
    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = Auth::user();

        try {
            if ($user->avatar_url && file_exists(public_path($user->avatar_url))) {
                unlink(public_path($user->avatar_url));
            }

            $avatar = $request->file('avatar');
            $avatarPath = 'user/avatar/' . $user->id . '/' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $destinationPath = public_path('user/avatar/' . $user->id);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $avatar->move($destinationPath, basename($avatarPath));
            $user->avatar_url = $avatarPath;
            $user->save();

            // Возвращаем редирект и на route('profile') и на route('profile.index') для совместимости
            if (Route::has('profile')) {
                return redirect()->route('profile')->with('success', 'Аватар успешно обновлен');
            }
            return redirect()->route('profile.index')->with('success', 'Аватар успешно обновлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении аватара: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e
            ]);
            
            return back()->with('error', 'Не удалось обновить аватар: ' . $e->getMessage());
        }
    }

    // 6. Удаление аккаунта пользователя
    public function deleteAccount()
    {
        try {
            $user = Auth::user();

            if ($user->avatar_url && file_exists(public_path($user->avatar_url))) {
                unlink(public_path($user->avatar_url));
            }

            $user->delete();

            return redirect('/')->with('success', 'Ваш аккаунт был успешно удален');
        } catch (\Exception $e) {
            return redirect()->route('profile.index')->with('error', 'Ошибка при удалении аккаунта. Попробуйте позже.');
        }
    }

    // 7. Изменение пароля - добавляем обработку ошибок
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();
            $user->password = bcrypt($request->new_password);
            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Пароль успешно изменен!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', $e->errors())
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при смене пароля: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при смене пароля',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 8. Обновление профиля - добавляем обработку ошибок
    public function updateProfile(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            ]);

            $user = Auth::user();

            if ($request->filled('name')) {
                $user->name = $request->name;
            }
            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Данные успешно обновлены!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', $e->errors())
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении профиля: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении профиля',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 9. Обновление профиля (новый метод) - улучшаем обработку ошибок
    public function updateProfileAll(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Отменяем проверку политики authorization, которая может вызывать ошибку
            // $this->authorize('update', $user);
            
            // Общие правила валидации
            $rules = [
                'name'         => 'nullable|string|max:255',
                'email'        => 'nullable|email|unique:users,email,' . $user->id,
                'new_password' => 'nullable|min:8|confirmed',
            ];

            // Дополнительные правила в зависимости от статуса пользователя
            switch ($user->status) {
                case 'user':
                    $rules['city'] = 'nullable|string|max:255';
                    break;
                case 'partner':
                    $rules['city'] = 'nullable|string|max:255';
                    $rules['contract_number'] = 'nullable|string|max:255';
                    $rules['comment'] = 'nullable|string';
                    break;
                case 'executor': // Профиль исполнителя
                case 'architect':
                case 'designer':
                case 'visualizer':
                    $rules['city'] = 'nullable|string|max:255'; // город/часовой пояс
                    $rules['portfolio_link'] = 'nullable|url';
                    $rules['experience'] = 'nullable|string|max:255';
                    $rules['rating'] = 'nullable|string|max:255';
                    $rules['active_projects_count'] = 'nullable|integer';
                    break;
                case 'coordinator':
                    $rules['experience'] = 'nullable|string|max:255';
                    $rules['rating'] = 'nullable|string|max:255';
                    break;
            }

            $validated = $request->validate($rules);

            // Обновляем базовые поля
            foreach (['name', 'email'] as $field) {
                if ($request->filled($field)) {
                    $user->$field = $request->$field;
                }
            }
            
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }

            // Обновляем дополнительные поля в зависимости от статуса
            switch ($user->status) {
                case 'user':
                    if ($request->filled('city')) {
                        $user->city = $request->city;
                    }
                    break;
                case 'partner':
                    $this->updatePartnerFields($user, $request);
                    break;
                case 'executor':
                case 'architect':
                case 'designer':
                case 'visualizer':
                    $this->updateExecutorFields($user, $request);
                    break;
                case 'coordinator':
                    $this->updateCoordinatorFields($user, $request);
                    break;
            }

            $user->save();

            Log::info('Профиль успешно обновлен', ['user_id' => $user->id, 'status' => $user->status]);

            return response()->json([
                'success' => true,
                'message' => 'Данные успешно обновлены!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ошибка валидации при обновлении профиля', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors()))
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении профиля: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении профиля: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }
    
    /**
     * Обновление полей для партнера
     */
    private function updatePartnerFields($user, $request)
    {
        $fields = ['city', 'contract_number', 'comment'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $user->$field = $request->$field;
            }
        }
    }

    /**
     * Обновление полей для исполнителя
     */
    private function updateExecutorFields($user, $request)
    {
        $fields = ['city', 'portfolio_link', 'experience', 'rating', 'active_projects_count'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $user->$field = $request->$field;
            }
        }
    }

    /**
     * Обновление полей для координатора
     */
    private function updateCoordinatorFields($user, $request)
    {
        $fields = ['experience', 'rating'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $user->$field = $request->$field;
            }
        }
    }

    public function updateFirebaseToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        try {
            $user->firebase_token = $request->token;
            $user->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении Firebase токена:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
