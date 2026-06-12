<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 舊系統(Grails motoranger)資料轉移。
 *
 * 來源為舊站 mysqldump 還原出的 MySQL(例如本地 docker),
 * 連線於執行時動態註冊,不寫入 config/database.php。
 *
 * 對應關係:
 *   store             → stores            (保留 id)
 *   brand             → brands            (保留 id)
 *   user              → users(員工)+ customers(客戶,保留 id)
 *   product           → vehicles          (保留 id)
 *   event             → repair_orders     (保留 id;END→completed, UNFIN→in_progress, APPRAISAL→quote)
 *   event_detail      → repair_order_items(保留 id)
 *   event.received_money → payments(一筆)
 *   *.main_image      → photos / parts.image_path(指向 storage/app/public/legacy/attachment/)
 *   store_cost_detail → store_expenses
 */
class MigrateLegacyData extends Command
{
    protected $signature = 'legacy:migrate
        {--fresh : 先 migrate:fresh 清空新資料庫}
        {--host=127.0.0.1} {--port=33061} {--database=motoranger_legacy}
        {--username=root} {--password=legacy}';

    protected $description = '將舊 Grails 系統資料轉移到新資料庫';

    protected const SRC = 'legacy_runtime';

    /** @var array<int, true> 員工 legacy user id */
    protected array $staffIds = [];

    /** @var array<string, int> 員工 username → 新 users.id */
    protected array $staffByUsername = [];

    /** @var array<int, true> 已建立的 customers id */
    protected array $customerIds = [];

    public function handle(): int
    {
        config(['database.connections.'.self::SRC => [
            'driver' => 'mysql',
            'host' => $this->option('host'),
            'port' => $this->option('port'),
            'database' => $this->option('database'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
            'charset' => 'utf8mb4',
        ]]);

        try {
            $this->src()->select('SELECT 1');
        } catch (\Throwable $e) {
            $this->error("無法連線舊資料庫:{$e->getMessage()}");
            $this->line('請先將備份載入本地 MySQL,例如:');
            $this->line('  docker run -d --name motoranger-legacy-db -e MYSQL_ROOT_PASSWORD=legacy \\');
            $this->line('    -e MYSQL_DATABASE=motoranger_legacy -p 33061:3306 mysql:8.4');
            $this->line('  gunzip -c database/legacy/motoranger-dump.sql.gz | docker exec -i motoranger-legacy-db \\');
            $this->line('    mysql -uroot -plegacy motoranger_legacy');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->info('清空新資料庫…');
            Artisan::call('migrate:fresh', ['--force' => true], $this->output);
        }

        DB::transaction(function () {
            $this->migrateStores();
            $this->migrateBrands();
            $this->migrateUsersAndCustomers();
            $this->migrateVehicles();
            $this->migrateParts();
            $this->migrateRepairOrders();
            $this->migrateRepairOrderItems();
            $this->migrateStoreExpenses();
        });

        $this->ensureLocalAdmin();
        $this->report();

        return self::SUCCESS;
    }

    protected function src(): \Illuminate\Database\Connection
    {
        return DB::connection(self::SRC);
    }

    /** 舊圖檔位置:attachment/{domain name}/{file},已同步至 public disk 的 legacy/ */
    protected function legacyImage(?string $ownerName, ?string $file): ?string
    {
        if (blank($ownerName) || blank($file)) {
            return null;
        }

        $path = "legacy/attachment/{$ownerName}/{$file}";

        return file_exists(storage_path("app/public/{$path}")) ? $path : null;
    }

    protected function migrateStores(): void
    {
        $rows = $this->src()->table('store')->orderBy('id')->get()->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->title ?: $s->name,
            'phone' => $s->telphone,
            'mobile' => $s->mobile,
            'fax' => $s->fax,
            'email' => $s->email,
            'address' => $s->address,
            'description' => $s->description,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('stores')->insert($rows);
        $this->info('店家:'.count($rows));
    }

    protected function migrateBrands(): void
    {
        $rows = $this->src()->table('brand')->orderBy('id')->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->title ?: $b->name,
            'type' => match ($b->type) {
                'MOTOCYCLE' => 'motorcycle',
                'CAR' => 'car',
                default => 'other',
            },
            'homepage' => $b->homepage,
            'description' => $b->description,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('brands')->insert($rows);
        $this->info('廠牌:'.count($rows));
    }

    /**
     * 舊 user 同時包含員工與客戶:
     *  - 有 ROLE_ADMIN / ROLE_MANERGER / ROLE_OPERATOR → 員工(users;ADMIN→admin,其餘→user)
     *  - 有車輛或維修記錄、或非員工 → 客戶(customers,保留舊 id)
     */
    protected function migrateUsersAndCustomers(): void
    {
        $roleByUser = [];
        $this->src()
            ->table('user_role')
            ->join('role', 'role.id', '=', 'user_role.role_id')
            ->select('user_role.user_id', 'role.authority')
            ->get()
            ->each(function ($r) use (&$roleByUser) {
                $roleByUser[$r->user_id][] = $r->authority;
            });

        $staffRoles = ['ROLE_ADMIN', 'ROLE_MANERGER', 'ROLE_MANAGER', 'ROLE_OPERATOR'];

        $ownsProduct = $this->src()->table('product')->whereNotNull('user_id')
            ->distinct()->pluck('user_id')->flip()->all();
        $hasEvent = $this->src()->table('event')->distinct()->pluck('user_id')->flip()->all();

        $users = [];
        $customers = [];

        foreach ($this->src()->table('user')->orderBy('id')->get() as $u) {
            $roles = $roleByUser[$u->id] ?? [];
            $isStaff = (bool) array_intersect($roles, $staffRoles);

            if ($isStaff) {
                $this->staffIds[$u->id] = true;
                $users[] = [
                    'legacy_username' => $u->username,
                    'row' => [
                        'name' => $u->title ?: $u->username,
                        'email' => $u->email ?: "{$u->username}@legacy.local",
                        'password' => Hash::make(Str::random(32)), // 舊雜湊不相容,需重設密碼
                        'role' => in_array('ROLE_ADMIN', $roles) ? UserRole::Admin->value : UserRole::User->value,
                        'phone' => $u->mobile ?: $u->telphone,
                        'store_id' => $u->store_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];
            }

            if (! $isStaff || isset($ownsProduct[$u->id]) || isset($hasEvent[$u->id])) {
                $this->customerIds[$u->id] = true;
                $customers[] = [
                    'id' => $u->id,
                    'name' => $u->title ?: $u->username,
                    'mobile' => $u->mobile,
                    'phone' => $u->telphone,
                    'email' => $u->email,
                    'address' => $u->address,
                    'description' => trim(($u->description ?? '').
                        ($isStaff ? ' (舊系統員工帳號)' : '')) ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($users as $staff) {
            // email 可能重複(舊系統員工另有客戶帳號),重複時改用 username 信箱
            $email = $staff['row']['email'];
            if (DB::table('users')->where('email', $email)->exists()) {
                $staff['row']['email'] = "{$staff['legacy_username']}@legacy.local";
            }
            $id = DB::table('users')->insertGetId($staff['row']);
            $this->staffByUsername[$staff['legacy_username']] = $id;
        }

        foreach (array_chunk($customers, 500) as $chunk) {
            DB::table('customers')->insert($chunk);
        }

        $this->info('員工:'.count($users).',客戶:'.count($customers));
    }

    protected function migrateVehicles(): void
    {
        $brandTypes = DB::table('brands')->pluck('type', 'id')->all();

        // 無車主的舊車輛掛在預留客戶下
        $orphanCustomerId = null;
        $count = 0;
        $photos = [];

        $this->src()->table('product')->orderBy('id')->chunk(500, function ($products) use (&$count, &$photos, &$orphanCustomerId, $brandTypes) {
            $rows = [];
            foreach ($products as $p) {
                $customerId = $p->user_id;
                if (! $customerId || ! isset($this->customerIds[$customerId])) {
                    $orphanCustomerId ??= DB::table('customers')->insertGetId([
                        'name' => '(未指定客戶)',
                        'description' => '舊系統無車主之車輛歸屬',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $customerId = $orphanCustomerId;
                }

                $rows[] = [
                    'id' => $p->id,
                    'customer_id' => $customerId,
                    'brand_id' => $p->brand_id,
                    'type' => $brandTypes[$p->brand_id] ?? 'motorcycle',
                    'plate_no' => $p->name,
                    'model' => $p->title,
                    'year' => $p->years ? (int) substr($p->years, 0, 4) : null,
                    'cc' => $p->cc,
                    'mileage' => max(0, $p->mileage),
                    'description' => $p->description,
                    'created_at' => $p->date_created,
                    'updated_at' => $p->last_updated,
                ];

                if ($path = $this->legacyImage($p->name, $p->main_image)) {
                    $photos[] = [
                        'imageable_type' => 'App\\Models\\Vehicle',
                        'imageable_id' => $p->id,
                        'path' => $path,
                        'created_at' => $p->date_created,
                        'updated_at' => $p->date_created,
                    ];
                }
            }
            DB::table('vehicles')->insert($rows);
            $count += count($rows);
        });

        foreach (array_chunk($photos, 500) as $chunk) {
            DB::table('photos')->insert($chunk);
        }

        $this->info("車輛:{$count}(照片 ".count($photos).')');
    }

    protected function migrateParts(): void
    {
        $count = 0;
        $this->src()->table('part')->orderBy('id')->chunk(500, function ($parts) use (&$count) {
            $rows = [];
            foreach ($parts as $p) {
                $rows[] = [
                    'id' => $p->id,
                    'store_id' => $p->store_id,
                    'sku' => $p->name,
                    'name' => $p->title ?: $p->name,
                    'cost' => max(0, $p->cost),
                    'price' => max(0, $p->price),
                    'stock_qty' => $p->stock_count,
                    'image_path' => $this->legacyImage($p->name, $p->main_image),
                    'description' => $p->description,
                    'created_at' => $p->date_created,
                    'updated_at' => $p->last_updated,
                ];
            }
            DB::table('parts')->insert($rows);
            $count += count($rows);
        });

        $this->info("零件:{$count}");
    }

    protected function migrateRepairOrders(): void
    {
        $vehicleOwner = DB::table('vehicles')->pluck('customer_id', 'id')->all();
        $count = 0;
        $payments = [];

        $this->src()->table('event')->orderBy('id')->chunk(500, function ($events) use (&$count, &$payments, $vehicleOwner) {
            $rows = [];
            foreach ($events as $e) {
                // 維修單客戶:優先用 event.user,否則用車主
                $customerId = isset($this->customerIds[$e->user_id])
                    ? $e->user_id
                    : ($vehicleOwner[$e->product_id] ?? null);

                if (! $customerId) {
                    continue; // 無法歸屬(理論上不會發生)
                }

                $subtotal = max(0, $e->total_price);
                $discount = max(0, $e->discount_money);

                $rows[] = [
                    'id' => $e->id,
                    'order_no' => $e->name,
                    'store_id' => $e->store_id,
                    'customer_id' => $customerId,
                    'vehicle_id' => $e->product_id,
                    'user_id' => $this->staffByUsername[$e->creator] ?? null,
                    'status' => match ($e->status) {
                        'APPRAISAL' => 'quote',
                        'UNFIN' => 'in_progress',
                        default => 'completed',
                    },
                    'date' => substr($e->date, 0, 10),
                    'mileage' => max(0, $e->mileage),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => max(0, $subtotal - $discount),
                    'paid_amount' => max(0, $e->received_money),
                    'note' => $e->description,
                    'completed_at' => $e->status === 'END' ? $e->last_updated : null,
                    'created_at' => $e->date_created,
                    'updated_at' => $e->last_updated,
                ];

                if ($e->received_money > 0) {
                    $payments[] = [
                        'repair_order_id' => $e->id,
                        'amount' => $e->received_money,
                        'method' => 'cash',
                        'paid_at' => substr($e->date, 0, 10),
                        'note' => '舊系統轉入',
                        'created_at' => $e->date_created,
                        'updated_at' => $e->date_created,
                    ];
                }
            }
            DB::table('repair_orders')->insert($rows);
            $count += count($rows);
        });

        foreach (array_chunk($payments, 500) as $chunk) {
            DB::table('payments')->insert($chunk);
        }

        $this->info("維修單:{$count}(收款 ".count($payments).')');
    }

    protected function migrateRepairOrderItems(): void
    {
        $orderIds = DB::table('repair_orders')->pluck('id')->flip()->all();
        $partIds = DB::table('parts')->pluck('id')->flip()->all();
        $count = 0;
        $photos = [];

        $this->src()->table('event_detail')->orderBy('id')->chunk(1000, function ($details) use (&$count, &$photos, $orderIds, $partIds) {
            $rows = [];
            foreach ($details as $d) {
                if (! isset($orderIds[$d->head_id])) {
                    continue;
                }
                $qty = max(1, (int) $d->qty);
                $price = max(0, $d->price);

                $rows[] = [
                    'id' => $d->id,
                    'repair_order_id' => $d->head_id,
                    'part_id' => isset($partIds[$d->part_id]) ? $d->part_id : null,
                    'type' => 'part',
                    'name' => $d->name,
                    'qty' => $qty,
                    'price' => $price,
                    'cost' => max(0, $d->cost),
                    'subtotal' => $qty * $price,
                    'note' => $d->description,
                    'created_at' => $d->date_created,
                    'updated_at' => $d->last_updated,
                ];

                if ($path = $this->legacyImage($d->name, $d->main_image)) {
                    $photos[] = [
                        'imageable_type' => 'App\\Models\\RepairOrder',
                        'imageable_id' => $d->head_id,
                        'path' => $path,
                        'caption' => $d->name,
                        'created_at' => $d->date_created,
                        'updated_at' => $d->date_created,
                    ];
                }
            }
            DB::table('repair_order_items')->insert($rows);
            $count += count($rows);
        });

        foreach (array_chunk($photos, 500) as $chunk) {
            DB::table('photos')->insert($chunk);
        }

        $this->info("維修明細:{$count}(照片 ".count($photos).')');
    }

    protected function migrateStoreExpenses(): void
    {
        $rows = $this->src()->table('store_cost_detail')->orderBy('id')->get()->map(fn ($c) => [
            'store_id' => $c->store_id,
            'title' => $c->title,
            'amount' => max(0, $c->cost),
            'date' => substr($c->date, 0, 10),
            'description' => $c->description,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('store_expenses')->insert($rows);
        $this->info('店家支出:'.count($rows));
    }

    /** 本地開發用管理員 */
    protected function ensureLocalAdmin(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@motoranger.test'],
            ['name' => '系統管理員', 'password' => 'password', 'role' => UserRole::Admin],
        );
    }

    protected function report(): void
    {
        $this->newLine();
        $this->table(['資料表', '筆數'], collect([
            'stores', 'users', 'customers', 'brands', 'vehicles', 'parts',
            'repair_orders', 'repair_order_items', 'payments', 'photos', 'store_expenses',
        ])->map(fn ($t) => [$t, DB::table($t)->count()]));
    }
}
