<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;

class PhotoUpload
{
    /**
     * 共用的「現場拍照 / 上傳」欄位:
     * 手機可直接開鏡頭、瀏覽器端先縮圖、儲存時再壓縮成約 512KB 的 JPEG。
     */
    public static function make(string $name = 'photos', string $directory = 'repair-photos'): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->panelLayout('grid')
            ->disk('public')
            ->directory($directory)
            ->imageEditor()
            ->helperText('點擊可開啟相機拍照或選擇相簿照片(可多張),上傳時會自動壓縮。')
            // 手機點擊直接開啟後鏡頭拍照
            ->extraInputAttributes(['capture' => 'environment'])
            ->maxSize(30720) // 30MB 安全上限(前端會先縮圖)
            // 上傳前於瀏覽器端縮圖,避免手機大圖卡住
            ->automaticallyResizeImagesMode('contain')
            ->automaticallyResizeImagesToWidth('1600')
            ->automaticallyResizeImagesToHeight('1600')
            ->automaticallyUpscaleImagesWhenResizing(false)
            // 儲存時以 GD 再壓縮成 JPEG,控制在約 512KB 內
            ->saveUploadedFileUsing(fn ($file) => ImageStorage::compress($file, $directory));
    }
}
