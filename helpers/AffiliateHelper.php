<?php namespace Affiliates\Helpers;

use Bbf\Helpers\UrlHelper;

class AffiliateHelper extends UrlHelper
{
    /**
     * @return the bbf program uri segment
     */
    public function uri()
    {
        return config('bbf.affiliatesUri', '/affiliates');
    }
}