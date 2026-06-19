<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditQuoteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_has_print_quote_action(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);
        $customer = Customer::create(['name' => '客戶']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'PRT-001']);
        $order = RepairOrder::create(['customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'date' => today()]);

        $this->get("/admin/repair-orders/{$order->id}/edit")
            ->assertOk()
            ->assertSee('列印估價單')
            ->assertSee(route('repair-orders.quote', $order));

        // 估價單列印頁本身可開啟
        $this->get(route('repair-orders.quote', $order))->assertOk()->assertSee($order->order_no);
    }

    public function test_quote_renders_for_order_without_customer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        // 無車主的車輛與維修單
        $vehicle = Vehicle::create(['plate_no' => 'NOC-777']);
        $order = RepairOrder::create(['vehicle_id' => $vehicle->id, 'date' => today()]);

        $this->assertNull($order->customer_id);

        $this->get(route('repair-orders.quote', $order))
            ->assertOk()
            ->assertSee($order->order_no);
    }
}
