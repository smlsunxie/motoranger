<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Part;
use App\Models\RepairOrder;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * 操作手冊截圖用的展示資料(僅供本地 docs 截圖,勿用於正式環境)。
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::firstOrCreate(['name' => '勝祥機車行'], [
            'phone' => '04-2222-3333',
            'mobile' => '0912-345-678',
            'address' => '台中市西區美村路一段 100 號',
            'tax_id' => '12345678',
        ]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@motoranger.test'],
            ['name' => '系統管理員', 'password' => bcrypt('password'), 'role' => 'admin'],
        );
        $admin->update(['name' => '系統管理員', 'store_id' => $store->id, 'role' => 'admin']);

        $paul = User::query()->firstOrCreate(
            ['email' => 'paul@motoranger.test'],
            ['name' => '保羅', 'password' => bcrypt('password'), 'role' => 'user'],
        );
        $paul->update(['store_id' => $store->id]);

        $brands = [];
        foreach (['三陽 SYM', '山葉 YAMAHA', '光陽 KYMCO', '本田 HONDA'] as $name) {
            $brands[$name] = Brand::firstOrCreate(['name' => $name], ['type' => 'motorcycle']);
        }

        foreach ([['機油 5W-40', 320, 180], ['前煞車皮', 250, 120], ['後煞車皮', 250, 120], ['空氣濾芯', 180, 90], ['火星塞', 150, 70], ['傳動皮帶', 680, 400], ['電瓶 GTX5L', 950, 600]] as [$n, $p, $c]) {
            Part::firstOrCreate(['name' => $n], ['store_id' => $store->id, 'unit' => '個', 'price' => $p, 'cost' => $c, 'stock_qty' => 20]);
        }

        $rows = [
            ['林志明', '0911-111-111', '三陽 SYM', '迪爵 125', 'ABC-1234', '黑', 18159],
            ['陳美惠', '0922-222-222', '山葉 YAMAHA', '勁戰六代', 'BMW-5678', '藍', 12450],
            ['黃國華', '0933-333-333', '光陽 KYMCO', '雷霆 150', 'CDE-9012', '紅', 26800],
            ['張淑芬', '0955-555-555', '本田 HONDA', 'PCX 160', 'DEF-3456', '白', 5200],
        ];

        foreach ($rows as $i => [$cname, $mobile, $bname, $model, $plate, $color, $mileage]) {
            $customer = Customer::firstOrCreate(['mobile' => $mobile], [
                'store_id' => $store->id, 'name' => $cname, 'address' => '台中市',
            ]);

            $vehicle = Vehicle::firstOrCreate(['plate_no' => $plate], [
                'customer_id' => $customer->id, 'brand_id' => $brands[$bname]->id,
                'model' => $model, 'cc' => 125, 'year' => 2021 + ($i % 3), 'color' => $color, 'mileage' => $mileage,
                'description' => '二手車',
            ]);

            if ($vehicle->repairOrders()->exists()) {
                continue;
            }

            foreach (range(0, $i % 3) as $k) {
                $order = RepairOrder::create([
                    'store_id' => $store->id, 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
                    'user_id' => $k % 2 ? $paul->id : $admin->id,
                    'status' => ['completed', 'in_progress', 'quote'][$k % 3],
                    'date' => now()->subDays($k * 45 + $i)->toDateString(),
                    'mileage' => max(0, $mileage - $k * 1500),
                    'note' => $k === 0 ? '定期保養,更換機油' : '客戶反映行進間有異音',
                ]);

                $part = Part::query()->inRandomOrder()->first();
                $order->items()->create(['part_id' => $part->id, 'type' => 'part', 'name' => $part->name, 'qty' => 1, 'price' => $part->price, 'cost' => $part->cost]);
                $order->items()->create(['type' => 'labor', 'name' => '工資', 'qty' => 1, 'price' => 300]);

                if ($k === 0) {
                    $order->payments()->create(['amount' => 200, 'method' => 'cash', 'paid_at' => now()->toDateString()]);
                }
            }
        }
    }
}
