<?php

use App\AdminPanel\Notifications\FlashBag;
use App\AdminPanel\Notifications\Notify;

test('notify queues structured notifications for inertia', function () {
    Notify::success('Saved')->action('View', '/admin/tests')->title('Done');

    $items = FlashBag::collect();

    expect($items)->toHaveCount(1)
        ->and($items[0]['type'])->toBe('success')
        ->and($items[0]['message'])->toBe('Saved')
        ->and($items[0]['title'])->toBe('Done')
        ->and($items[0]['action']['label'])->toBe('View')
        ->and($items[0]['action']['href'])->toBe('/admin/tests');
});

test('legacy with success flash is collected', function () {
    session()->flash('success', 'Legacy ok');

    $items = FlashBag::collect();

    expect($items)->toHaveCount(1)
        ->and($items[0]['type'])->toBe('success')
        ->and($items[0]['message'])->toBe('Legacy ok');
});
