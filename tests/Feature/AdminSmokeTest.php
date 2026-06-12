<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_resource_pages_render(): void
    {
        $this->actingAs($this->admin);

        foreach ([
            '/admin',
            '/admin/repair-orders',
            '/admin/repair-orders/create',
            '/admin/customers',
            '/admin/vehicles',
            '/admin/parts',
            '/admin/brands',
            '/admin/stores',
            '/admin/store-expenses',
            '/admin/users',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_repair_order_totals_recalculate(): void
    {
        $customer = Customer::create(['name' => '測試客戶']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'TST-001']);

        $order = RepairOrder::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'date' => today(),
            'discount' => 100,
        ]);

        $order->items()->create(['name' => '零件A', 'qty' => 2, 'price' => 300]);
        $order->items()->create(['name' => '工資', 'type' => 'labor', 'qty' => 1, 'price' => 500]);
        $order->payments()->create(['amount' => 500, 'paid_at' => today()]);

        $order->refresh();

        $this->assertSame(1100, (int) $order->subtotal);
        $this->assertSame(1000, (int) $order->total);
        $this->assertSame(500, (int) $order->paid_amount);
        $this->assertSame(500, $order->balance);
        $this->assertStringStartsWith('RO-', $order->order_no);
    }

    public function test_quote_pages(): void
    {
        $customer = Customer::create(['name' => '測試客戶']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'TST-001']);
        $order = RepairOrder::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'date' => today(),
        ]);

        // 後台列印頁需登入
        $this->get(route('repair-orders.quote', $order))->assertRedirect();
        $this->actingAs($this->admin)
            ->get(route('repair-orders.quote', $order))
            ->assertOk()
            ->assertSee($order->order_no);

        // 公開頁需簽名
        $this->get("/q/{$order->id}")->assertForbidden();
        $signed = \Illuminate\Support\Facades\URL::signedRoute('quote.public', ['repairOrder' => $order], absolute: false);
        $this->get($signed)->assertOk()->assertSee($order->order_no);

        // 客人於公開頁留下備注
        $noteAction = \Illuminate\Support\Facades\URL::signedRoute('quote.public.note', ['repairOrder' => $order], absolute: false);
        $this->post("/q/{$order->id}/notes", ['content' => '未簽名不可留言'])->assertForbidden();
        $this->post($noteAction, ['content' => '請順便檢查後輪胎'])->assertRedirect();

        $note = $order->notes()->first();
        $this->assertSame('請順便檢查後輪胎', $note->content);
        $this->assertTrue($note->author->is($customer));
    }
}
