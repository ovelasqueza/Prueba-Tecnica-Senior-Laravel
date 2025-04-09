<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_name',
        'country_code',
        'temperature',
        'weather_condition',
        'wind_speed',
        'humidity',
        'local_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}