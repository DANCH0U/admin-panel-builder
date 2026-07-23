<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('category')->nullable()->after('status');
            $table->unsignedTinyInteger('priority')->default(1)->after('category');
            $table->unsignedSmallInteger('score')->nullable()->after('priority');
            $table->boolean('is_featured')->default(false)->after('score');
            $table->boolean('is_public')->default(true)->after('is_featured');
            $table->json('tags')->nullable()->after('is_public');
            $table->json('metadata')->nullable()->after('tags');
            $table->timestamp('published_at')->nullable()->after('metadata');
            $table->text('notes')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'category',
                'priority',
                'score',
                'is_featured',
                'is_public',
                'tags',
                'metadata',
                'published_at',
                'notes',
            ]);
        });
    }
};
