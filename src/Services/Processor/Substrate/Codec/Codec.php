<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec;

use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Decoder as FuelTankDecoder;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Encoder as FuelTankEncoder;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec as BaseCodec;

class Codec extends BaseCodec
{
    protected Encoder $fuelTankEncoder;
    protected Decoder $fuelTankDecoder;

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->fuelTankEncoder = new FuelTankEncoder($this->scaleInstance);
        $this->fuelTankDecoder = new FuelTankDecoder($this->scaleInstance);
    }

    /**
     * Get the encoder.
     */
    public function encode(): Encoder
    {
        return $this->fuelTankEncoder;
    }

    /**
     * Get the decoder.
     */
    public function decode(): Decoder
    {
        return $this->fuelTankDecoder;
    }
}
