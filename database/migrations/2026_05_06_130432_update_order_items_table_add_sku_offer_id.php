<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Переименовываем product_id в sku
            $table->renameColumn('product_id', 'sku');
            // Добавляем offer_id (артикул продавца, строковый)
            $table->string('offer_id')->nullable()->after('sku');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('sku', 'product_id');
            $table->dropColumn('offer_id');
        });
    }
};
