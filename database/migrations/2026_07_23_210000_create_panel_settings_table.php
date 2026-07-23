<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('panel', 64)->unique();
            $table->string('app_name')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('navbar_title')->nullable();
            $table->boolean('show_theme_toggle')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_settings');
    }
};
