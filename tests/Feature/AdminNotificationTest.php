<?php

use App\Models\User;
use App\Notifications\AdminNotification;

test('admin notification is stored in the database', function () {
    $user = User::factory()->create();

    AdminNotification::send(
        $user,
        'Post published.',
        title: 'Posts',
        type: 'success',
        url: '/admin/posts',
    );

    expect($user->notifications)->toHaveCount(1)
        ->and($user->unreadNotifications)->toHaveCount(1);

    $data = $user->notifications->first()->data;

    expect($data['message'])->toBe('Post published.')
        ->and($data['title'])->toBe('Posts')
        ->and($data['type'])->toBe('success')
        ->and($data['url'])->toBe('/admin/posts');
});

test('authenticated user can list and mark notifications', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $user->notify(new AdminNotification('Hello', title: 'Welcome', type: 'info'));

    $this->actingAs($user)
        ->getJson('/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('notifications.0.message', 'Hello');

    $id = $user->notifications()->first()->id;

    $this->actingAs($user)
        ->postJson("/notifications/{$id}/read")
        ->assertOk()
        ->assertJsonPath('unread_count', 0);

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);
});
