<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('city_name');
            $table->string('country_code', 2);
            $table->decimal('temperature', 5, 2);
            $table->string('weather_condition');
            $table->decimal('wind_speed', 5, 2);
            $table->integer('humidity');
            $table->timestamp('local_time');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('search_histories');
    }
};