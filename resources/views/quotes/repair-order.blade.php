<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>估價單 {{ $order->order_no }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: "PingFang TC", "Microsoft JhengHei", "Noto Sans TC", sans-serif;
        font-size: 13px; color: #111; padding: 24px; max-width: 800px; margin: 0 auto;
    }
    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111; padding-bottom: 12px; }
    .header h1 { font-size: 22px; letter-spacing: 4px; }
    .store-info { font-size: 12px; color: #444; margin-top: 4px; line-height: 1.6; }
    .meta { text-align: right; font-size: 12px; line-height: 1.8; }
    .meta .order-no { font-size: 14px; font-weight: bold; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin: 14px 0; font-size: 13px; }
    .info-grid span.label { color: #666; margin-right: 6px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #999; padding: 6px 8px; }
    th { background: #f0f0f0; font-weight: 600; text-align: left; }
    td.num, th.num { text-align: right; }
    .totals { margin-top: 10px; display: flex; justify-content: flex-end; }
    .totals table { width: 260px; }
    .totals td { border: none; padding: 3px 8px; }
    .totals .grand td { font-size: 16px; font-weight: bold; border-top: 1px solid #111; }
    .footer { margin-top: 28px; display: flex; justify-content: space-between; align-items: flex-end; }
    .sign { display: flex; gap: 48px; }
    .sign div { border-top: 1px solid #111; padding-top: 6px; width: 140px; text-align: center; }
    .qr { text-align: center; font-size: 11px; color: #666; }
    .note-box { margin-top: 12px; font-size: 12px; color: #444; }
    .print-btn {
        position: fixed; top: 16px; right: 16px; padding: 10px 20px; font-size: 14px;
        background: #f59e0b; color: #fff; border: none; border-radius: 8px; cursor: pointer;
    }
    @media print { .print-btn { display: none; } body { padding: 0; } }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨 列印</button>

<div class="header">
    <div>
        <h1>{{ $order->store?->name ?? 'Motoranger' }} 估價單</h1>
        <div class="store-info">
            @if($order->store?->address) 地址:{{ $order->store->address }}<br> @endif
            @if($order->store?->phone) 電話:{{ $order->store->phone }} @endif
            @if($order->store?->tax_id) 統編:{{ $order->store->tax_id }} @endif
        </div>
    </div>
    <div class="meta">
        <div class="order-no">單號:{{ $order->order_no }}</div>
        <div>日期:{{ $order->date->format('Y-m-d') }}</div>
        <div>狀態:{{ $order->status->getLabel() }}</div>
    </div>
</div>

<div class="info-grid">
    <div><span class="label">客戶</span>{{ $order->customer->name }}</div>
    <div><span class="label">聯絡電話</span>{{ $order->customer->mobile ?? $order->customer->phone }}</div>
    <div><span class="label">車牌</span>{{ $order->vehicle->plate_no }}</div>
    <div><span class="label">車輛</span>{{ trim(($order->vehicle->brand?->name ?? '').' '.$order->vehicle->model) }}</div>
    <div><span class="label">進廠里程</span>{{ number_format($order->mileage) }} km</div>
    <div><span class="label">承辦技師</span>{{ $order->user?->name }}</div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:36px">#</th>
            <th>項目</th>
            <th style="width:60px">類型</th>
            <th class="num" style="width:60px">數量</th>
            <th class="num" style="width:90px">單價</th>
            <th class="num" style="width:100px">小計</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->type->getLabel() }}</td>
            <td class="num">{{ $item->qty }}</td>
            <td class="num">{{ number_format($item->price) }}</td>
            <td class="num">{{ number_format($item->subtotal) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <table>
        <tr><td>小計</td><td class="num">$ {{ number_format($order->subtotal) }}</td></tr>
        @if($order->discount > 0)
        <tr><td>折扣</td><td class="num">- $ {{ number_format($order->discount) }}</td></tr>
        @endif
        <tr class="grand"><td>合計</td><td class="num">$ {{ number_format($order->total) }}</td></tr>
        @if($order->paid_amount > 0)
        <tr><td>已收</td><td class="num">$ {{ number_format($order->paid_amount) }}</td></tr>
        <tr><td>未收</td><td class="num">$ {{ number_format($order->balance) }}</td></tr>
        @endif
    </table>
</div>

@if($order->note)
<div class="note-box"><strong>備注:</strong>{{ $order->note }}</div>
@endif

<div class="footer">
    <div class="sign">
        <div>客戶簽名</div>
        <div>經手人</div>
    </div>
    @if($qrcode)
    <div class="qr">
        {!! $qrcode !!}
        <div>掃碼查看維修單</div>
    </div>
    @endif
</div>

</body>
</html>
