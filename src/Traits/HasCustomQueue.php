<?php

namespace Enjin\Platform\FuelTanks\Traits;

trait HasCustomQueue
{
    protected function setQueue(): void
    {
        $this->onQueue(config('enjin-platform-fuel-tanks.queue'));
    }
}
