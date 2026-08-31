<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->integer('month')->nullable()->change();
            $table->integer('year')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->integer('month')->nullable(false)->change();
            $table->integer('year')->nullable(false)->change();
        });
    }
};