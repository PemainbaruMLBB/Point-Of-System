<?php

test('the application routes visitors to the dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
