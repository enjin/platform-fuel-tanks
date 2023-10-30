<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DispatchAndTouchMutation;

class DispatchAndTouchTest extends DispatchTest
{
    /**
     * The graphql method.
     */
    protected string $method = 'DispatchAndTouch';

    public function test_it_can_dispatch(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateParams()
        );

        $encodedCall = DispatchAndTouchMutation::getEncodedCall($params);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DispatchAndTouchMutation::getEncodableParams(...$params)) . $encodedCall . '00'
        );
    }
}
