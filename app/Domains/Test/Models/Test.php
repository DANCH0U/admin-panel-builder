<?php

namespace App\Domains\Test\Models;

use App\Traits\HasUUID;
use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /** @use HasFactory<TestFactory> */
    use HasFactory, HasUUID;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'description',
        'status',
        'category',
        'priority',
        'score',
        'is_featured',
        'is_public',
        'tags',
        'metadata',
        'published_at',
        'notes',
        'cover_image',
        'checklist',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'tags' => 'array',
            'metadata' => 'array',
            'checklist' => 'array',
            'published_at' => 'datetime',
            'priority' => 'integer',
            'score' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function newFactory(): TestFactory
    {
        return TestFactory::new();
    }
}
