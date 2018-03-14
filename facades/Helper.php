<?php namespace Affiliates\Facades;

use October\Rain\Support\Facade;

class Helper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @see \Backend\Helpers\Backend
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'affiliates.helper';
    }
}
