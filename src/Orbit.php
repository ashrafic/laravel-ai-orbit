<?php

namespace Ashraf\Orbit;

use Ashraf\Orbit\Support\OrbitConfig;

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
