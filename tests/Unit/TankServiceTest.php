<?php

namespace Enjin\Platform\FuelTanks\Tests\Unit;

use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Services\TankService;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Traits\CreateCollectionData;
use Enjin\Platform\FuelTanks\Tests\TestCase;

class TankServiceTest extends TestCase
{
    use CreateCollectionData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->createCollectionData();
    }

    public function test_it_can_create_records()
    {
        $service = resolve(TankService::class);
        $tank = FuelTank::factory()->make(['owner_wallet_id' => $this->wallet->id]);
        $this->assertNotEmpty($model = $service->store($tank->toArray()));
        $this->assertNotEmpty($service->get($model->public_key));
        $this->assertTrue($service->insert(
            FuelTank::factory()->make(['owner_wallet_id' => $this->wallet->id])->toArray()
        ));
    }
}
