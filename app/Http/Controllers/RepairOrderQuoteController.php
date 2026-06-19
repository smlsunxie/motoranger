<?php

namespace App\Http\Controllers;

use App\Models\RepairOrder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\URL;

class RepairOrderQuoteController extends Controller
{
    /** 估價單列印頁(後台,需登入) */
    public function __invoke(RepairOrder $repairOrder)
    {
        $repairOrder->load(['customer', 'vehicle.brand', 'items', 'store', 'user']);

        $publicUrl = url(URL::signedRoute('quote.public', ['repairOrder' => $repairOrder], absolute: false));

        return view('quotes.repair-order', [
            'order' => $repairOrder,
            'qrcode' => $this->qrcodeSvg($publicUrl),
        ]);
    }

    /** 客人掃 QR Code 看的公開頁(簽名網址) */
    public function publicShow(RepairOrder $repairOrder)
    {
        abort_unless(request()->hasValidRelativeSignature(), 403);

        $repairOrder->load(['customer', 'vehicle.brand', 'items', 'store', 'user', 'photos', 'notes.author']);

        return view('quotes.repair-order', [
            'order' => $repairOrder,
            'qrcode' => null,
            'public' => true,
            'noteAction' => URL::signedRoute('quote.public.note', ['repairOrder' => $repairOrder], absolute: false),
        ]);
    }

    /** 客人於公開頁留下備注 */
    public function addNote(RepairOrder $repairOrder)
    {
        abort_unless(request()->hasValidRelativeSignature(), 403);
        abort_unless($repairOrder->customer !== null, 403);

        $data = request()->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $repairOrder->notes()->create([
            'content' => $data['content'],
            'author_type' => $repairOrder->customer->getMorphClass(),
            'author_id' => $repairOrder->customer_id,
        ]);

        return back()->with('note_saved', true);
    }

    protected function qrcodeSvg(string $content): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle(120, 0),
            new SvgImageBackEnd,
        ));

        return $writer->writeString($content);
    }
}
