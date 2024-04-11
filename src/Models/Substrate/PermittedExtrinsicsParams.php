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
    protected ?array $extrinsics;

    /**
     * Creates a new instance.
     */
    public function __construct(?array $extrinsics = [])
    {
        $this->extrinsics = array_map(
            function ($extrinsic) {
                $palletName = array_key_first($extrinsic);
                $methodName = array_key_first($extrinsic[$palletName]);

                return $palletName . '.' . $methodName;
            },
            $extrinsics
        );
    }

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        return new self(
            extrinsics: Arr::get($params, 'PermittedExtrinsics.extrinsics') ?? Arr::get($params, 'PermittedExtrinsics')
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        ray($this->extrinsics);

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
