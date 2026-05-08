<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Revolution\Voicevox\VoicevoxServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            VoicevoxServiceProvider::class,
            AiServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        //
    }
}
