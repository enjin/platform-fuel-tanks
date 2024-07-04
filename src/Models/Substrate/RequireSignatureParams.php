<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Enjin\BlockchainTools\HexConverter;

class RequireSignatureParams extends FuelTankRules
{
    /**
     * Creates a new instance.
     */
    public function __construct(
        public string $signature,
    ) {}

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        return new self(
            signature: $params['RequireSignature'],
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        return ['RequireSignature' => $this->signature];
    }

    public function toArray(): array
    {
        return ['RequireSignature' => $this->toArray()];
    }

    public function validate(string $signature): bool
    {
        return ctype_xdigit($signature) && strlen(HexConverter::unPrefix($signature)) === 16 ;
    }
}
