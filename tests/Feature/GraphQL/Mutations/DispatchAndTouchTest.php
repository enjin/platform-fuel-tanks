<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

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
        $this->assertEquals(
            $response['encodedData'],
            $this->service->dispatchAndTouch($params)->encoded_data
        );
    }
}
