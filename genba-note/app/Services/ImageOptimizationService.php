<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 申し送り画像の保存・リサイズ・最適化を担うサービスクラスの骨組み。
 *
 * 現状はアップロードを media_disk へ保存する実装を提供し、
 * Intervention Image 等によるリサイズ／圧縮は TODO として拡張ポイントを残す。
 *
 * ディスクは config('filesystems.media_disk') を参照するため、
 * .env の MEDIA_DISK を s3 に切り替えるだけでクラウド移行可能。
 */
class ImageOptimizationService
{
    /**
     * 申し送り画像をストレージへ保存し、相対パスを返す。
     *
     * @param  UploadedFile  $file  アップロードされた画像
     * @param  string  $directory  保存先ディレクトリ（例: memos/2026/08/11）
     * @return string ディスク上の相対パス
     */
    public function storeMemoImage(UploadedFile $file, ?string $directory = null): string
    {
        $directory ??= 'memos/'.now()->format('Y/m/d');
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        // TODO: ここでリサイズ・WebP 変換・EXIF 除去などの最適化を行う
        // $optimizedBinary = $this->optimize($file);

        $path = $file->storeAs($directory, $filename, $this->disk());

        return $path;
    }

    /**
     * 画像バイナリの最適化（骨組み）。
     *
     * Intervention Image / GD / Imagick などを用いて実装予定。
     *
     * @param  UploadedFile  $file
     * @return string 最適化後のバイナリ文字列
     */
    public function optimize(UploadedFile $file): string
    {
        // TODO: 最大辺 1920px へのリサイズ、品質 80% での JPEG/WebP 圧縮を実装する
        return file_get_contents($file->getRealPath()) ?: '';
    }

    /**
     * 指定パスのファイルを削除する。
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk())->delete($path);
    }

    /**
     * メディア保存に使用するディスク名を返す。
     */
    protected function disk(): string
    {
        return (string) config('filesystems.media_disk', 'public');
    }
}
