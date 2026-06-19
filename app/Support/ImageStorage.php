<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorage
{
    /**
     * 壓縮並儲存圖片:輸出 JPEG,最長邊縮到 maxDim 內,
     * 逐步降低品質直到檔案 <= maxBytes(預設約 512KB)。
     * 無法以 GD 處理時退回原檔儲存。回傳相對路徑。
     */
    public static function compress(
        UploadedFile $file,
        string $directory,
        int $maxBytes = 524288, // 512 KB
        int $maxDim = 1600,
        string $disk = 'public',
    ): string {
        $path = $file->getRealPath();

        if (! $path || ! function_exists('imagecreatefromstring')) {
            return $file->store($directory, $disk);
        }

        $contents = @file_get_contents($path);
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;

        // 非 GD 可解析的圖片(如 HEIC)→ 原檔儲存,避免上傳失敗
        if ($source === false) {
            return $file->store($directory, $disk);
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // 等比例縮到最長邊 <= maxDim
        $scale = min(1, $maxDim / max($width, $height));
        $targetW = max(1, (int) round($width * $scale));
        $targetH = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetW, $targetH);
        // JPEG 不支援透明,先填白底
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetW, $targetH, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetW, $targetH, $width, $height);

        // 逐步降品質直到符合大小上限
        $binary = '';
        foreach ([85, 78, 70, 62, 55, 48, 40] as $quality) {
            ob_start();
            imagejpeg($canvas, null, $quality);
            $binary = (string) ob_get_clean();

            if (strlen($binary) <= $maxBytes) {
                break;
            }
        }

        $storedPath = trim($directory, '/').'/'.Str::uuid()->toString().'.jpg';
        Storage::disk($disk)->put($storedPath, $binary);

        return $storedPath;
    }
}
