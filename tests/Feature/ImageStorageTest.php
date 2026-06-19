<?php

namespace Tests\Feature;

use App\Support\ImageStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageStorageTest extends TestCase
{
    public function test_large_image_is_compressed_under_target(): void
    {
        Storage::fake('public');

        // 產生一張 4000x3000、含雜訊的高品質 JPEG(壓縮前遠大於 512KB)
        $img = imagecreatetruecolor(4000, 3000);
        for ($i = 0; $i < 60000; $i++) {
            imagesetpixel(
                $img,
                random_int(0, 3999),
                random_int(0, 2999),
                imagecolorallocate($img, random_int(0, 255), random_int(0, 255), random_int(0, 255))
            );
        }
        ob_start();
        imagejpeg($img, null, 100);
        $data = (string) ob_get_clean();

        $tmp = tempnam(sys_get_temp_dir(), 'img').'.jpg';
        file_put_contents($tmp, $data);
        $file = new UploadedFile($tmp, 'camera.jpg', 'image/jpeg', null, true);

        $path = ImageStorage::compress($file, 'repair-photos');

        Storage::disk('public')->assertExists($path);

        $stored = Storage::disk('public')->get($path);
        $this->assertLessThanOrEqual(512 * 1024, strlen($stored), '壓縮後應 <= 512KB');

        [$w, $h] = getimagesizefromstring($stored);
        $this->assertLessThanOrEqual(1600, max($w, $h), '最長邊應縮到 1600 以內');
        $this->assertStringEndsWith('.jpg', $path);
    }
}
