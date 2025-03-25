<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mantax559\LaravelHelpers\Helpers\TableHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-activities.table'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger(TableHelper::getForeignKey(config('laravel-activities.user_model')))->unsigned()->nullable();
            $table->string('table');
            $table->string('record_id');
            $table->string('event');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('locale')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::table(config('laravel-activities.table'), function (Blueprint $table) {
            $table->foreign(TableHelper::getForeignKey(config('laravel-activities.user_model')))->references('id')->on(TableHelper::getName(config('laravel-activities.user_model')));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-activities.table'));
    }
};
