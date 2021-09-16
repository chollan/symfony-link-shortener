<?php

namespace App\Service;

use ConvertApi\ConvertApi;

class ConvertApiFactory
{
    /**
     * Create a ConvertApi object
     * @param string $secret
     * @return ConvertApi
     */
    public static function createConvertApi (string $secret): ConvertApi
    {
        $convertApi = new ConvertApi();
        $convertApi->setApiSecret($secret);
        return $convertApi;
    }
}