<?php

namespace App\Http\Controllers;

use App\Enums\RepairOrderStatus;
use App\Models\RepairOrder;

class FrontController extends Controller
{
    public function home()
    {
        $base = fn () => RepairOrder::query()
            ->with(['vehicle.photos', 'user', 'customer'])
            ->orderByDesc('date');

        return view('front.home', [
            'inProgress' => $base()->where('status', RepairOrderStatus::InProgress)->limit(8)->get(),
            'completed' => $base()->where('status', RepairOrderStatus::Completed)->limit(8)->get(),
        ]);
    }
}
