<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feature 11: promised payment date on orders
        // Feature 6: track print count for "Duplicate" label
        // Feature 12: delivery status on orders
        Schema::table('orders', function (Blueprint $table) {
            $table->date('promised_payment_date')->nullable()->after('due');
            $table->integer('print_count')->default(0)->after('promised_payment_date');
            $table->boolean('is_delivered')->default(false)->after('print_count');
            $table->string('delivery_note')->nullable()->after('is_delivered');
        });

        // Feature 1 & 7: return_qty and return_amount on order_products
        Schema::table('order_products', function (Blueprint $table) {
            $table->integer('return_qty')->default(0)->after('quantity');
            $table->double('return_amount')->default(0)->after('return_qty');
            $table->double('manual_price')->nullable()->after('price'); // Feature 2: manual price override
        });

        // Feature 5: sub_category_id on products (parent category as sub-category)
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id');
            $table->foreign('sub_category_id')->references('id')->on('categories')->nullOnDelete();
        });

        // Feature 4 & 8: city on customers for due payment report
        Schema::table('customers', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promised_payment_date', 'print_count', 'is_delivered', 'delivery_note']);
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn(['return_qty', 'return_amount', 'manual_price']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn('sub_category_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
