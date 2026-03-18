<?php
/**
 * Shared certificate PDF generator.
 * Returns raw PDF binary — does NOT stream to browser.
 *
 * @param array  $cert   Row from form_submissions
 * @param string $type   'training' | 'participation' | 'internship'
 * @param array  $layout Decoded layout array from certificate_layouts.layout_json
 * @return string        Raw PDF bytes
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateCertificatePdf(array $cert, string $type, array $layout): string
{
    $root = dirname(__DIR__);

    $bgFiles = [
        'training'      => $root . '/uploads/traning.png',
        'participation' => $root . '/uploads/particepation.jpg',
        'internship'    => $root . '/uploads/intenship.png',
    ];
    $bgPath = $bgFiles[$type] ?? '';
    $bgUri  = '';
    if ($bgPath && file_exists($bgPath)) {
        $bgMime = (str_ends_with($bgPath, '.jpg') || str_ends_with($bgPath, '.jpeg'))
            ? 'image/jpeg' : 'image/png';
        $bgUri = 'data:' . $bgMime . ';base64,' . base64_encode(file_get_contents($bgPath));
    }

    $courses_parsed = json_decode($cert['courses_selected'] ?? '[]', true);
    $program_name   = is_array($courses_parsed) ? implode(', ', $courses_parsed) : '';
    $fmt = fn($d) => $d ? date('jS F Y', strtotime($d)) : '';
    $vars = [
        '{name}'         => $cert['name'],
        '{program_name}' => $program_name,
        '{days}'         => $cert['days'] ?? '',
        '{start_date}'   => $fmt($cert['start_date']),
        '{end_date}'     => $fmt($cert['end_date']),
        '{date}'         => $fmt($cert['certificate_date']),
    ];

    $CW = 2790;
    $CH = 1800;

    $textHtml = '';
    foreach ($layout as $field => $cfg) {
        // cfg.text may contain HTML for inline rich-text formatting — do NOT escape it
        $text = strtr($cfg['text'] ?? '', $vars);
        $left       = round(($cfg['left']  / 100) * $CW, 2);
        $top        = round(($cfg['top']   / 100) * $CH, 2);
        $width      = round(($cfg['width'] / 100) * $CW, 2);
        $fontSize   = (int)($cfg['fontSize']  ?? 14);
        $fontWeight = $cfg['fontWeight'] ?? 'normal';
        $fontStyle  = $cfg['fontStyle']  ?? 'normal';
        $fontFamily = $cfg['fontFamily'] ?? 'DejaVu Sans';
        $color      = $cfg['color']      ?? '#000000';
        $textAlign  = $cfg['textAlign']  ?? 'left';

        $textHtml .= "<div style=\""
            . "position:absolute;"
            . "left:{$left}px;"
            . "top:{$top}px;"
            . "width:{$width}px;"
            . "font-size:{$fontSize}px;"
            . "font-weight:{$fontWeight};"
            . "font-style:{$fontStyle};"
            . "font-family:'{$fontFamily}';"
            . "color:{$color};"
            . "text-align:{$textAlign};"
            . "line-height:1.4;"
            . "box-sizing:content-box;"
            . "padding:0;margin:0;"
            . "\">{$text}</div>\n";
    }

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { margin: 0; padding: 0; size: {$CW}px {$CH}px; }
html { margin: 0; padding: 0; }
body { margin: 0; padding: 0; width: {$CW}px; height: {$CH}px; position: relative; overflow: hidden; }
img.cert-bg { position: absolute; top: 0; left: 0; width: {$CW}px; height: {$CH}px; display: block; }
</style>
</head>
<body>
<img class="cert-bg" src="{$bgUri}" alt="">
{$textHtml}
</body>
</html>
HTML;

    $fontCacheDir = $root . '/assets/fonts/cache/';
    if (!is_dir($fontCacheDir)) {
        mkdir($fontCacheDir, 0755, true);
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('fontDir', $fontCacheDir);
    $options->set('fontCache', $fontCacheDir);
    $options->set('chroot', [$root]);

    $dompdf = new Dompdf($options);

    $fontsDir = $root . '/assets/fonts/';
    $fm = $dompdf->getFontMetrics();
    $fm->registerFont(['family' => 'Kelvinch',      'weight' => 'bold',   'style' => 'normal'], $fontsDir . 'Kelvinch-Bold.otf');
    $fm->registerFont(['family' => 'Montserrat',    'weight' => 'normal', 'style' => 'normal'], $fontsDir . 'Montserrat-Regular.ttf');
    $fm->registerFont(['family' => 'Montserrat',    'weight' => 'bold',   'style' => 'normal'], $fontsDir . 'Montserrat-Bold.ttf');
    $fm->registerFont(['family' => 'Raleway',       'weight' => 'normal', 'style' => 'normal'], $fontsDir . 'Raleway-Regular.ttf');
    $fm->registerFont(['family' => 'Pinyon Script', 'weight' => 'normal', 'style' => 'normal'], $fontsDir . 'PinyonScript-Regular.ttf');
    $fm->registerFont(['family' => 'Pinyon Script', 'weight' => 'bold',   'style' => 'normal'], $fontsDir . 'PinyonScript-Regular.ttf');

    $dompdf->loadHtml($html);
    $dompdf->setPaper([0, 0, 2092.5, 1350]);
    $dompdf->render();

    return $dompdf->output();
}
