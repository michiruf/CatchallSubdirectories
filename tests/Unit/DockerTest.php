<?php

it('can run docker', function () {
    expect(`docker run --rm hello-world`)
        ->not->toBeNull()
        ->toContain('Hello from Docker!');
})->group('long-running');
