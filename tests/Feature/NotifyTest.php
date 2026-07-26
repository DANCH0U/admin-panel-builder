<?php

use App\AdminPanel\Notifications\FlashBag;
use App\AdminPanel\Notifications\Notify;

test('notify queues structured notifications for inertia', function () {
    Notify::success('Saved')->title('Done');

    $items = FlashBag::collect();

    expect($items)->toHaveCount(1)
        ->and($items[0]['type'])->toBe('success')
        ->and($items[0]['message'])->toBe('Saved')
        ->and($items[0]['title'])->toBe('Done');
});

test('legacy with success flash is collected', function () {
    session()->flash('success', 'Legacy ok');

    $items = FlashBag::collect();

    expect($items)->toHaveCount(1)
        ->and($items[0]['type'])->toBe('success')
        ->and($items[0]['message'])->toBe('Legacy ok');
});
