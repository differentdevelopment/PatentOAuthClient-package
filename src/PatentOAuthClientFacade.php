<?php

namespace Different\PatentOAuthClient;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Different\PatentOAuthClient\Skeleton\SkeletonClass
 */
class PatentOAuthClientFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'patent-oauth-client';
    }
}
