<?php

test('example test', function () {
    expect(true)->toBeTrue();
});

test('the application returns a successful response', function () {
    $response = $this->get('/');
    expect($response->status())
        ->toBeGreaterThanOrEqual(200)
        ->toBeLessThan(400);
});
