<?php

namespace Savannabits\Dockavel;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Savannabits\Dockavel\Skeleton\SkeletonClass
 */
class DockavelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dockavel';
    }
}
