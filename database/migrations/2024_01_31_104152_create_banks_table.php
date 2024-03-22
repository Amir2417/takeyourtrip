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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('bank_name');
            $table->string('currency_name',20);
            $table->string('currency_code',20);
            $table->string('currency_symbol',20)->nullable();
            $table->decimal('min_limit',28,8,true)->unsigned()->default(0);
            $table->decimal('max_limit',28,8,true)->unsigned()->default(0);
            $table->decimal('percent_charge',28,8,true)->unsigned()->default(0);
            $table->decimal('fixed_charge',28,8,true)->unsigned()->default(0);
            $table->decimal('rate',28,8,true)->unsigned()->default(0);
            $table->text('desc',500)->nullable();
            $table->text('input_fields',1000)->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banks');
    }
};
