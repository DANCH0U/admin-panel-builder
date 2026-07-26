<?php

test('home page is available', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
