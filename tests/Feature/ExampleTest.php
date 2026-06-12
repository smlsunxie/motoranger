<?php

namespace Tests\Feature;

use App\Enums\RepairOrderStatus;
use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_home_renders_repair_activity(): void
    {
        $customer = Customer::create(['name' => '王小明']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'ABC-123']);
        RepairOrder::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'date' => today(),
            'status' => RepairOrderStatus::InProgress,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('線上摩托維修記錄')
            ->assertSee('維修中')
            ->assertSee('ABC-123')
            ->assertSee('王**')        // 客戶名打碼
            ->assertDontSee('王小明'); // 不洩漏全名
    }
}
