<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Package;
use Enjin\Platform\Services\Processor\Substrate\Codec\Encoder as BaseEncoder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PermittedExtrinsicsParams extends FuelTankRules
{
    /**
     * Creates a new instance.
     */
    public function __construct(
        public ?array $extrinsics = [],
    ) {
    }

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        return new self(
            extrinsics: array_map(
                fn ($extrinsic) => is_string($extrinsic) ? $extrinsic :
                        collect(BaseEncoder::getCallIndexKeys())
                            ->filter(
                                fn ($item) => $item
                                ==
                                sprintf(
                                    '%s.%s',
                                    HexConverter::hexToString(Arr::get($extrinsic, 'palletName')),
                                    HexConverter::hexToString(Arr::get($extrinsic, 'extrinsicName')),
                                ),
                            )->keys()->first(),
                Arr::get($params, 'PermittedExtrinsics.extrinsics', [])
            ),
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        $encodedData = '07'; // TODO: This should come from the metadata and not hardcode it.
        $encodedData .= HexConverter::intToHex(count($this->extrinsics) * 4);
        $encodedData .= collect($this->extrinsics)->reduce(fn ($data, $mutation) => Str::of($data)->append($this->getEncodedData($mutation))->toString(), '');

        return [
            'PermittedExtrinsics' =>  ['extrinsics' => $encodedData],
        ];
    }

    protected function getEncodedData(string $mutationName): string
    {
        $transactionMutation = Package::getClassesThatImplementInterface(PlatformBlockchainTransaction::class)
            ->filter(fn ($class) => Str::contains(class_basename($class), $mutationName))->first();

        return HexConverter::unPrefix(TransactionSerializer::encode((new $transactionMutation())->getMethodName(), $transactionMutation::getEncodableParams()));
    }
}
