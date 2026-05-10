<?php

namespace Ashraf\LaravelAiOrbit;

use Ashraf\LaravelAiOrbit\Support\OrbitConfig;

class Orbit
{
    /**
     * Get the configured dashboard URI path.
     */
    public function path(): string
    {
        return OrbitConfig::path();
    }
}
