<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 店家(車行)
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // 店名
            $table->string('phone')->nullable();             // 電話
            $table->string('mobile')->nullable();            // 手機
            $table->string('fax')->nullable();               // 傳真
            $table->string('email')->nullable();
            $table->string('address')->nullable();           // 地址
            $table->string('tax_id')->nullable();            // 統一編號
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();         // 估價單列印用 logo
            $table->timestamps();
        });

        // 員工(後台登入者)— 擴充 Laravel 預設 users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role')->default('user')->after('password'); // admin / user
            $table->string('phone')->nullable()->after('role');
        });

        // 廠牌
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // 廠牌名稱
            $table->string('type')->default('motorcycle');   // motorcycle / car / other
            $table->string('homepage')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 客戶
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->index();                 // 姓名
            $table->string('mobile')->nullable()->index();   // 手機(搜尋主鍵)
            $table->string('phone')->nullable();             // 市話
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->date('birthday')->nullable();
            $table->string('line_id')->nullable();
            $table->string('password')->nullable();          // 前台登入(可為空)
            $table->text('description')->nullable();         // 內部備注
            $table->timestamps();
            $table->softDeletes();
        });

        // 車輛(舊系統的 Product)
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('motorcycle');   // motorcycle / car / other
            $table->string('plate_no')->index();             // 車牌號碼(搜尋主鍵)
            $table->string('model')->nullable();             // 車型
            $table->unsignedSmallInteger('year')->nullable();// 出廠年份
            $table->unsignedInteger('cc')->nullable();       // 排氣量
            $table->string('color')->nullable();
            $table->string('vin')->nullable();               // 車身號碼
            $table->string('engine_no')->nullable();         // 引擎號碼
            $table->unsignedBigInteger('mileage')->default(0); // 最新里程
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 零件目錄
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable()->index();      // 料號
            $table->string('name')->index();                 // 品名
            $table->string('spec')->nullable();              // 規格
            $table->string('unit')->default('個');           // 單位
            $table->unsignedBigInteger('cost')->default(0);  // 成本
            $table->unsignedBigInteger('price')->default(0); // 售價
            $table->integer('stock_qty')->default(0);        // 庫存
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 維修單(含估價單階段;舊系統的 Event)
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();            // 單號 RO-202606-0001
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // 承辦技師
            $table->string('status')->default('quote')->index(); // quote/approved/in_progress/completed/delivered/cancelled
            $table->date('date');                            // 進廠日期
            $table->unsignedBigInteger('mileage')->default(0);   // 進廠里程
            $table->unsignedBigInteger('subtotal')->default(0);  // 小計(由明細計算)
            $table->unsignedBigInteger('discount')->default(0);  // 折扣
            $table->unsignedBigInteger('total')->default(0);     // 應收 = subtotal - discount
            $table->unsignedBigInteger('paid_amount')->default(0); // 已收(由收款記錄計算)
            $table->text('note')->nullable();                // 維修描述
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 維修明細(舊系統的 EventDetail)
        Schema::create('repair_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('part');         // part 零件 / labor 工資
            $table->string('name');                          // 項目名稱
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('price')->default(0); // 單價
            $table->unsignedBigInteger('cost')->default(0);  // 成本
            $table->unsignedBigInteger('subtotal')->default(0); // qty * price
            $table->string('note')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // 收款記錄
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('method')->default('cash');       // cash / transfer / card / other
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        // 照片(維修單 / 車輛 / 零件 皆可掛,拍照上傳)
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable');                     // imageable_type + imageable_id
            $table->string('path');
            $table->string('caption')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // 拍攝者
            $table->timestamps();
        });

        // 備注(客戶 / 車輛 / 維修單 皆可掛;作者可為員工或客人)
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');                       // notable_type + notable_id
            $table->nullableMorphs('author');                // User(員工) 或 Customer(客人)
            $table->text('content');
            $table->timestamps();
        });

        // 店家支出(營業報表用;舊系統的 StoreCostDetail)
        Schema::create('store_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedBigInteger('amount');
            $table->date('date')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_expenses');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('repair_order_items');
        Schema::dropIfExists('repair_orders');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('brands');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropColumn(['role', 'phone']);
        });
        Schema::dropIfExists('stores');
    }
};
