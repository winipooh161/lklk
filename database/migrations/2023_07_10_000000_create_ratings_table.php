<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('rated_user_id'); // ID оцениваемого пользователя
            $table->unsignedBigInteger('rater_user_id'); // ID пользователя, оставившего оценку
            $table->integer('score'); // Оценка от 1 до 5
            $table->text('comment')->nullable(); // Комментарий к оценке
            $table->string('role'); // Роль оцениваемого (architect, designer, visualizer, coordinator)
            $table->timestamps();

            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('rated_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rater_user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Один пользователь может оставить только одну оценку другому пользователю в рамках одной сделки
            $table->unique(['deal_id', 'rated_user_id', 'rater_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
