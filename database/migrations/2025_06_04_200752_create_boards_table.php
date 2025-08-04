<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('code')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('boards');
    }
};
