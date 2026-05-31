<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number'); // Contoh: A1, B12
            $table->enum('type', ['A', 'B']);
            $table->dateTime('called_at')->nullable(); // Panggilan Terakhir
            $table->unsignedBigInteger('user_id')->nullable(); // id caller (kasir)
            $table->string('caller')->nullable(); // Kasir mana yang panggil
            $table->timestamps(); // Created_at jadi Waktu Cetak
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
