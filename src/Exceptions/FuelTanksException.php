<?php

namespace Enjin\Platform\FuelTanks\Exceptions;

use Enjin\Platform\Exceptions\PlatformException;

class FuelTanksException extends PlatformException
{
    /**
     * Get the exception's category.
     */
    public function getCategory(): string
    {
        return 'Platform Fuel Tanks';
    }
}
