<?php

namespace Enjin\Platform\FuelTanks\Services;

use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\Services\Database\TransactionService as DatabaseTransactionService;
use Enjin\Platform\Services\Database\WalletService;

class TransactionService extends DatabaseTransactionService
{
    use HasEncodableTokenId;

    /**
     * Create a new service instance.
     */
    public function __construct(
        public readonly Codec $codec,
        public readonly WalletService $wallet
    ) {
    }
}
