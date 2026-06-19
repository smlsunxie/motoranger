<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\RepairOrders\Pages\CreateRepairOrder;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RepairCreateOptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_create_option_creates_vehicle(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);
        $customer = Customer::create(['name' => '既有車主']);
        $brand = Brand::create(['name' => 'YAMAHA']);

        Livewire::test(CreateRepairOrder::class)
            ->mountFormComponentAction('vehicle_id', 'createOption')
            ->assertOk()
            ->setFormComponentActionData([
                'plate_no' => 'NEW-001',
                'customer_id' => $customer->id,
                'brand_id' => $brand->id,
                'model' => 'BWS',
            ])
            ->callMountedFormComponentAction()
            ->assertHasNoFormComponentActionErrors();

        $v = Vehicle::where('plate_no', 'NEW-001')->first();
        $this->assertNotNull($v, '車輛應被建立');
        $this->assertSame($customer->id, (int) $v->customer_id);
        $this->assertSame($brand->id, (int) $v->brand_id);
    }

    public function test_vehicle_create_option_allows_no_customer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        Livewire::test(CreateRepairOrder::class)
            ->mountFormComponentAction('vehicle_id', 'createOption')
            ->assertOk()
            ->setFormComponentActionData([
                'plate_no' => 'NOCUST-001',
                // 不填車主
            ])
            ->callMountedFormComponentAction()
            ->assertHasNoFormComponentActionErrors();

        $v = Vehicle::where('plate_no', 'NOCUST-001')->first();
        $this->assertNotNull($v, '無車主也可建立車輛');
        $this->assertNull($v->customer_id);
    }
}
