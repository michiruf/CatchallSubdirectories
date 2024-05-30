<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Ddeboer\Imap\ConnectionInterface;

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function establishImapTestConnection(bool $bindAgain = false): ConnectionInterface
{
    $connection = app(ConnectionInterface::class, [
        'hostname' => 'localhost',
        'port' => 40993,
        'username' => 'debug@local',
        'password' => 'debug',
        'validateCert' => false,
    ]);

    // May bind again to not depend on the parameters
    // Ensure that the connection gets closed manually in that case, since __destruct will not get called
    if ($bindAgain) {
        app()->bind(ConnectionInterface::class, fn () => $connection);
    }

    return $connection;
}
