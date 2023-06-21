<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

abstract class FuelTankRules
{
    /**
     * Get the kind array for this model.
     *
     * @return array
     */
    public function toKind(): array
    {
        return [
            str_replace('Params', '', (new \ReflectionClass($this))->getShortName()) => null,
        ];
    }
}
