<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'common_id',
        'commercial_id',
        'client_name',
        'name',
        'client_phone',
        'total_sum',
        'measuring_cost',
        'project_budget',
        'status',
        'registration_link',
        'registration_link_expiry',
        'user_id',
        'coordinator_id',
        'registration_token',
        'registration_token_expiry',
        'avatar_path',
        'link',
        'created_date',
        'client_city',
        'client_email',
        'client_info',
        'execution_comment',
        'comment',
        'project_number',
        'order_stage',
        'price_service_option',
        'rooms_count_pricing',
        'execution_order_comment',
        'execution_order_file',
        'client_timezone',
        'office_partner_id',
        'client_account_link',
        'measurement_comments',
        'measurements_file',
        'brief',
        'start_date',
        'project_duration',
        'project_end_date',
        'architect_id',
        'final_floorplan',
        'designer_id',
        'final_collage',
        'visualizer_id',
        'visualization_link',
        'final_project_file',
        'work_act',
        'archicad_file',
        'contract_number',
        'contract_attachment',
        'deal_note',
        'object_type',
        'package',
        'completion_responsible',
        'office_equipment',
        'stage',
        'coordinator_score',
        'has_animals',
        'has_plants',
        'object_style',
        'measurements',
        'rooms_count',
        'deal_end_date',
        'payment_date',
        'chat_group_id', // Добавляем поле chat_group_id
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }

    public function brief()
    {
        return $this->belongsTo(Common::class, 'common_id');
    }

    public function briefs()
    {
        return $this->hasMany(Common::class, 'deal_id');
    }

    public function commercials()
    {
        return $this->hasMany(Commercial::class, 'deal_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'deal_user', 'deal_id', 'user_id');
    }

    public function coordinators()
    {
        return $this->users()->wherePivot('role', 'coordinator');
    }

    public function responsibles()
    {
        return $this->belongsToMany(User::class, 'deal_user');
    }

    public function allUsers()
    {
        return $this->users()->wherePivotIn('role', ['responsible', 'coordinator']);
    }

    public function dealFeeds()
    {
        return $this->hasMany(DealFeed::class);
    }

    public function changeLogs()
    {
        return $this->hasMany(DealChangeLog::class);
    }

    /**
     * Связь с групповым чатом
     */
    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    /**
     * Альтернативное отношение для прямого доступа к pivot-таблице.
     */
    public function usersPivot()
    {
        return $this->hasMany(DealUser::class);
    }

    /**
     * Рейтинги, связанные со сделкой
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Проверка, оценил ли указанный пользователь исполнителя в этой сделке
     */
    public function hasRatingFrom($raterUserId, $ratedUserId)
    {
        $hasRating = $this->ratings()
            ->where('rater_user_id', $raterUserId)
            ->where('rated_user_id', $ratedUserId)
            ->exists();
        
        // Логируем результат для отладки
        \Illuminate\Support\Facades\Log::debug('Проверка наличия оценки', [
            'deal_id' => $this->id,
            'rater_user_id' => $raterUserId,
            'rated_user_id' => $ratedUserId,
            'has_rating' => $hasRating
        ]);
        
        return $hasRating;
    }
}
