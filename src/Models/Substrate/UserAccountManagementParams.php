<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

class UserAccountManagementParams
{
    /**
     * Creates a new instance.
     */
    public function __construct(
        public bool $tankReservesExistentialDeposit = false,
        public bool $tankReservesAccountCreationDeposit = false,
    ) {}

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        return [
            'tankReservesExistentialDeposit' => $this->tankReservesExistentialDeposit,
            'tankReservesAccountCreationDeposit' => $this->tankReservesAccountCreationDeposit,
        ];
    }
}
