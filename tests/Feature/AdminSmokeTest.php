<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\VehiclesRelationManager;
use App\Filament\Resources\RepairOrders\Pages\CreateRepairOrder;
use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\Resources\Vehicles\Pages\VehicleProfile;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Filament\Widgets\VehicleSearchWidget;
use App\Models\Customer;
use App\Models\Part;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
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

    public function test_dashboard_search_returns_matching_vehicles(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['name' => '阿明', 'mobile' => '0911222333']);
        $match = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'ABC-123']);
        RepairOrder::create(['customer_id' => $customer->id, 'vehicle_id' => $match->id, 'date' => today(), 'user_id' => $this->admin->id]);
        Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'ZZZ-999']);

        Livewire::test(VehicleSearchWidget::class)
            ->set('search', 'ABC')
            ->call('submitSearch')
            ->assertSet('searched', true)
            ->assertSee('ABC-123')
            ->assertSee(VehicleResource::getUrl('profile', ['record' => $match]))
            ->assertDontSee('ZZZ-999');
    }

    public function test_dashboard_search_create_vehicle_when_not_found(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(VehicleSearchWidget::class)
            ->set('search', 'NEWPLATE-1')
            ->call('submitSearch')
            ->callAction('createVehicle', data: ['plate_no' => 'NEWPLATE-1'])
            ->assertHasNoActionErrors();

        $v = Vehicle::where('plate_no', 'NEWPLATE-1')->first();
        $this->assertNotNull($v, '找不到時應可直接建立車輛');
        $this->assertNull($v->customer_id);
    }

    public function test_vehicle_profile_page_renders_history(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['name' => '車主', 'mobile' => '0911000111']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'PROF-1', 'mileage' => 2000]);
        $order = RepairOrder::create(['customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'date' => today(), 'user_id' => $this->admin->id]);
        $order->items()->create(['name' => '換機油', 'qty' => 1, 'price' => 300]);

        $this->get(VehicleResource::getUrl('profile', ['record' => $vehicle]))
            ->assertOk()
            ->assertSee('PROF-1')
            ->assertSee('車主')
            ->assertSee($order->order_no)
            ->assertSee('換機油')
            ->assertSee('建立維修紀錄')
            ->assertSee('重拍車輛圖')
            // 每筆維修紀錄有列印估價單連結
            ->assertSee(route('repair-orders.quote', $order), false);
    }

    public function test_customer_view_page_shows_vehicles(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['name' => '陳大文', 'mobile' => '0922333444']);
        Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'CUST-CAR-1']);

        // 檢視頁可開啟,顯示客戶資料與編輯按鈕
        $this->get(CustomerResource::getUrl('view', ['record' => $customer]))
            ->assertOk()
            ->assertSee('陳大文')
            ->assertSee('0922333444')
            ->assertSee('編輯');

        // 關聯車輛由 VehiclesRelationManager 呈現
        Livewire::test(VehiclesRelationManager::class, [
            'ownerRecord' => $customer,
            'pageClass' => ViewCustomer::class,
        ])
            ->assertSee('CUST-CAR-1');
    }

    public function test_repair_order_view_page_renders_with_edit(): void
    {
        $this->actingAs($this->admin);

        $vehicle = Vehicle::create(['plate_no' => 'VIEW-1']);
        $order = RepairOrder::create(['vehicle_id' => $vehicle->id, 'date' => today(), 'user_id' => $this->admin->id]);
        $order->items()->create(['name' => '換胎', 'qty' => 1, 'price' => 800]);

        $this->get(RepairOrderResource::getUrl('view', ['record' => $order]))
            ->assertOk()
            ->assertSee($order->order_no)
            ->assertSee('換胎')
            ->assertSee('編輯')
            ->assertSee(route('repair-orders.quote', $order), false);
    }

    public function test_vehicle_profile_create_order_action(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['name' => '車主']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'PROF-2', 'mileage' => 1000]);

        Livewire::test(VehicleProfile::class, ['record' => $vehicle->id])
            ->callAction('createOrder', data: [
                'status' => 'in_progress',
                'date' => today()->toDateString(),
                'mileage' => 1800,
                'items' => [
                    ['type' => 'labor', 'name' => '檢查', 'qty' => 1, 'price' => 200, 'cost' => 0, 'photos' => ['repair-photos/i1.jpg']],
                ],
            ])
            ->assertHasNoActionErrors();

        $order = RepairOrder::where('vehicle_id', $vehicle->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame($customer->id, (int) $order->customer_id);
        $this->assertSame(200, (int) $order->total);
        $this->assertSame(1800, (int) $vehicle->refresh()->mileage);
        // 維修項目可附照片
        $this->assertSame(1, $order->items()->first()->photos()->count());
    }

    public function test_vehicle_profile_create_order_auto_creates_part(): void
    {
        $this->actingAs($this->admin);

        $vehicle = Vehicle::create(['plate_no' => 'PART-1']);

        Livewire::test(VehicleProfile::class, ['record' => $vehicle->id])
            ->callAction('createOrder', data: [
                'status' => 'quote',
                'date' => today()->toDateString(),
                'items' => [
                    // 零件類型、未挑既有零件,只輸入名稱
                    ['type' => 'part', 'name' => '不存在的化油器', 'qty' => 1, 'price' => 1200, 'cost' => 800],
                ],
            ])
            ->assertHasNoActionErrors();

        $part = Part::where('name', '不存在的化油器')->first();
        $this->assertNotNull($part, '不存在的零件應被自動建立');
        $this->assertSame(1200, (int) $part->price);

        $order = RepairOrder::where('vehicle_id', $vehicle->id)->latest('id')->first();
        $this->assertSame($part->id, (int) $order->items()->first()->part_id);
    }

    public function test_repair_order_form_auto_creates_part(): void
    {
        $this->actingAs($this->admin);

        $vehicle = Vehicle::create(['plate_no' => 'PART-2']);

        Livewire::test(CreateRepairOrder::class)
            ->fillForm([
                'vehicle_id' => $vehicle->id,
                'status' => 'quote',
                'date' => today()->toDateString(),
                'items' => [
                    ['type' => 'part', 'name' => '新零件墊片', 'qty' => 2, 'price' => 50, 'cost' => 20],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $part = Part::where('name', '新零件墊片')->first();
        $this->assertNotNull($part, '維修單表單也應自動建立零件');

        $order = RepairOrder::where('vehicle_id', $vehicle->id)->latest('id')->first();
        $this->assertSame($part->id, (int) $order->items()->first()->part_id);
    }

    public function test_vehicle_profile_rephoto_action(): void
    {
        $this->actingAs($this->admin);

        $vehicle = Vehicle::create(['plate_no' => 'PROF-3']);

        Livewire::test(VehicleProfile::class, ['record' => $vehicle->id])
            // 點圖片會呼叫 mountAction('rephoto')
            ->mountAction('rephoto')
            ->assertActionMounted('rephoto')
            ->setActionData(['photos' => ['vehicle-photos/x.jpg', 'vehicle-photos/y.jpg']])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame(2, $vehicle->photos()->count());
    }

    public function test_vehicle_can_be_created_without_customer(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateVehicle::class)
            ->fillForm([
                'plate_no' => 'NOCUST-9',
                'type' => 'motorcycle',
                'mileage' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $v = Vehicle::where('plate_no', 'NOCUST-9')->first();
        $this->assertNotNull($v);
        $this->assertNull($v->customer_id);
    }

    public function test_repair_order_create_without_customer(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['name' => '小華']);
        $vehicle = Vehicle::create(['customer_id' => $customer->id, 'plate_no' => 'XYZ-999']);

        // 只選車輛、不填客戶,客戶應自動帶入
        Livewire::test(CreateRepairOrder::class)
            ->fillForm([
                'vehicle_id' => $vehicle->id,
                'status' => 'quote',
                'date' => today()->toDateString(),
                'items' => [
                    ['type' => 'labor', 'name' => '檢查', 'qty' => 1, 'price' => 200, 'cost' => 0],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = RepairOrder::where('vehicle_id', $vehicle->id)->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertSame($customer->id, (int) $order->customer_id);
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
        $signed = URL::signedRoute('quote.public', ['repairOrder' => $order], absolute: false);
        $this->get($signed)->assertOk()->assertSee($order->order_no);

        // 客人於公開頁留下備注
        $noteAction = URL::signedRoute('quote.public.note', ['repairOrder' => $order], absolute: false);
        $this->post("/q/{$order->id}/notes", ['content' => '未簽名不可留言'])->assertForbidden();
        $this->post($noteAction, ['content' => '請順便檢查後輪胎'])->assertRedirect();

        $note = $order->notes()->first();
        $this->assertSame('請順便檢查後輪胎', $note->content);
        $this->assertTrue($note->author->is($customer));
    }
}
