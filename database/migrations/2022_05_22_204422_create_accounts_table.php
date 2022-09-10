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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('login');
            $table->string('nickname');
            $table->string('email');
            $table->string('password');
            $table->string('server');
            $table->string('provider');
            $table->integer('elo')->default(0);
            $table->integer('level')->default(30);
            $table->integer('ea');
            $table->integer('status')->default(1);
            $table->json('skins');
            $table->date('birthday');
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
        Schema::dropIfExists('accounts');
    }
};
