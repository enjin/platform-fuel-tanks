<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec;

use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Decoder as FuelTankDecoder;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Encoder as FuelTankEncoder;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec as BaseCodec;

class Codec extends BaseCodec
{
    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->encoder = new FuelTankEncoder($this->scaleInstance);
        $this->decoder = new FuelTankDecoder($this->scaleInstance);
    }

    /**
     * Get the encoder.
     */
    public function encoder(): Encoder
    {
        return $this->encoder;
    }

    /**
     * Get the decoder.
     */
    public function decoder(): Decoder
    {
        return $this->decoder;
    }
}
