<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Part;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::firstOrCreate(
            ['name' => 'Motoranger 車業'],
            ['phone' => '02-12345678', 'address' => '台北市'],
        );

        User::firstOrCreate(
            ['email' => 'admin@motoranger.test'],
            [
                'name' => '系統管理員',
                'password' => 'password',
                'role' => UserRole::Admin,
                'store_id' => $store->id,
            ],
        );

        foreach ([
            ['name' => 'YAMAHA', 'type' => VehicleType::Motorcycle],
            ['name' => 'HONDA', 'type' => VehicleType::Motorcycle],
            ['name' => 'SYM', 'type' => VehicleType::Motorcycle],
            ['name' => 'KYMCO', 'type' => VehicleType::Motorcycle],
            ['name' => 'SUZUKI', 'type' => VehicleType::Motorcycle],
            ['name' => 'TOYOTA', 'type' => VehicleType::Car],
        ] as $brand) {
            Brand::firstOrCreate(['name' => $brand['name']], $brand);
        }

        if (app()->environment('local') && Customer::count() === 0) {
            $customer = Customer::create([
                'store_id' => $store->id,
                'name' => '王小明',
                'mobile' => '0912345678',
                'address' => '台北市中山區',
            ]);

            Vehicle::create([
                'customer_id' => $customer->id,
                'brand_id' => Brand::where('name', 'YAMAHA')->value('id'),
                'type' => VehicleType::Motorcycle,
                'plate_no' => 'ABC-1234',
                'model' => '勁戰六代',
                'cc' => 125,
                'mileage' => 12000,
            ]);

            foreach ([
                ['name' => '機油 (10W-40)', 'price' => 350, 'cost' => 200, 'stock_qty' => 50, 'unit' => '瓶'],
                ['name' => '空氣濾芯', 'price' => 250, 'cost' => 120, 'stock_qty' => 20],
                ['name' => '前煞車皮', 'price' => 400, 'cost' => 220, 'stock_qty' => 30, 'unit' => '組'],
                ['name' => '齒輪油', 'price' => 150, 'cost' => 80, 'stock_qty' => 40, 'unit' => '瓶'],
            ] as $part) {
                Part::create($part + ['store_id' => $store->id]);
            }
        }
    }
}
