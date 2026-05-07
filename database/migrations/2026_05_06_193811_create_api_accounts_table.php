<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название магазина
            $table->string('client_id');
            $table->text('api_key'); // будет зашифрован
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_accounts');
    }
};
