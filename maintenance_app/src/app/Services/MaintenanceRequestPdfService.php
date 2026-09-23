<?php

namespace App\Services;

use App\Models\Trouble;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MaintenanceRequestPdfService
{
    private const FONT_CANDIDATES = [
        '/usr/share/fonts/opentype/ipafont-gothic/ipag.ttf',
        '/usr/share/fonts/truetype/fonts-japanese-gothic.ttf',
        '/usr/share/fonts/opentype/ipafont-gothic/ipagp.ttf',
    ];

    public function download(Trouble $trouble): SymfonyResponse
    {
        $this->ensureFontStorage();

        $fontPath = $this->resolveFontPath();
        $filename = sprintf('保全依頼表_TMQ-%06d.pdf', $trouble->id);

        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'ipag');
        $options->set('fontDir', storage_path('fonts'));
        $options->set('fontCache', storage_path('fonts'));
        $options->set('chroot', [
            base_path(),
            storage_path(),
            '/usr/share/fonts',
            '/',
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->getFontMetrics()->registerFont(
            ['family' => 'ipag', 'style' => 'normal', 'weight' => 'normal'],
            $fontPath
        );

        $html = view('pdf.maintenance_request', [
            'trouble' => $trouble,
            'fontFamily' => 'ipag',
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function ensureFontStorage(): void
    {
        $dir = storage_path('fonts');

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }
    }

    protected function resolveFontPath(): string
    {
        foreach (self::FONT_CANDIDATES as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('日本語フォントが見つかりません。fonts-ipafont-gothic をインストールしてください。');
    }
}
