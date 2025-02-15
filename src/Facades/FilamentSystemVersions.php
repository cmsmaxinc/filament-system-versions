<?php

namespace Cmsmaxinc\FilamentSystemVersions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersions
 */
class FilamentSystemVersions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersions::class;
    }
}
