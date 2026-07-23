<?php

test('home redirects to admin', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(admin_path());
});
