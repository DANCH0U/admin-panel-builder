<?php

namespace App\Domains\Test\Actions;

use App\Domains\Test\Models\Test;

class CreateTestAction
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function execute(array $validated): Test
    {
        return Test::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);
    }
}
