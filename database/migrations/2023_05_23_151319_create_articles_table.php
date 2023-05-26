<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('code_product')->unique();
            $table->decimal('sale_price', $precision = 10, $scale = 4);
            $table->decimal('cost_price', $precision = 10, $scale = 4);
            $table->bigInteger('stock');
            $table->string('clase');
            $table->string('marca');
            $table->string('family');
            $table->string('state');
            $table->integer('warehouse_type_id');
            $table->integer('created_at_user_id')->nullable();
            $table->integer('updated_at_user_id')->nullable();
            $table->integer('deleted_at_user_id')->nullable();
            $table->timestamp('expiration_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
