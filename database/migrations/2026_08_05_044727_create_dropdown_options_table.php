<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dropdown_options', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // kategori_pidana, jenis_narkotika, satuan, tempat_penyimpanan, keterangan_tahap
            $table->string('label');
            $table->enum('form_target', ['3A', '3C', 'Keduanya'])->default('Keduanya');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dropdown_options');
    }
};