<?php
// Minimal, dependency-free PDF generator for simple text reports.
// Pure PHP, no Composer — works on shared hosting. Core font Helvetica (ASCII text; non-ASCII
// is transliterated). Enough for one-page clinical reports (headings, labelled fields, wrapped
// text, and an embedded JPEG image such as a doctor signature).

class SimplePdf
{
    private float $w = 595.28;   // A4 width in points
    private float $h = 841.89;   // A4 height in points
    private string $content = '';
    private array $images = [];  // [ ['data'=>bytes,'w'=>int,'h'=>int,'cs'=>'DeviceRGB'|'DeviceGray'|'DeviceCMYK'], ... ]

    public function line(float $x1, float $yTop1, float $x2, float $yTop2, float $width = 0.5): void
    {
        $this->content .= sprintf(
            "%.2f w %.2f %.2f m %.2f %.2f l S\n",
            $width, $x1, $this->h - $yTop1, $x2, $this->h - $yTop2
        );
    }

    public function text(float $xLeft, float $yTop, string $str, float $size = 10, bool $bold = false): void
    {
        $this->content .= sprintf(
            "BT %s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $bold ? '/F2' : '/F1', $size, $xLeft, $this->h - $yTop, $this->esc($str)
        );
    }

    // Word-wrap $str, drawing each line; returns the yTop just below the block.
    public function textBlock(float $xLeft, float $yTop, string $str, float $size = 10, int $maxChars = 95, float $leading = 14): float
    {
        $str = str_replace(["\r\n", "\r"], "\n", (string) $str);
        if (trim($str) === '') {
            $this->text($xLeft, $yTop, '(none recorded)', $size);
            return $yTop + $leading;
        }
        foreach (explode("\n", $str) as $para) {
            $para = rtrim($para);
            if ($para === '') { $yTop += $leading; continue; }
            $line = '';
            foreach (explode(' ', $para) as $word) {
                $try = $line === '' ? $word : "$line $word";
                if (strlen($try) > $maxChars && $line !== '') {
                    $this->text($xLeft, $yTop, $line, $size);
                    $yTop += $leading;
                    $line = $word;
                } else {
                    $line = $try;
                }
            }
            if ($line !== '') { $this->text($xLeft, $yTop, $line, $size); $yTop += $leading; }
        }
        return $yTop;
    }

    // Embed a JPEG image (bytes) at (xLeft, yTop-from-top) drawn $wPt x $hPt points.
    // Returns true if embedded, false if the data was not a usable JPEG (caller can fall back).
    public function image(string $jpegData, float $xLeft, float $yTop, float $wPt, float $hPt): bool
    {
        $dims = $this->jpegInfo($jpegData);
        if ($dims === null) {
            return false;
        }
        $this->images[] = ['data' => $jpegData, 'w' => $dims['w'], 'h' => $dims['h'], 'cs' => $dims['cs']];
        $n = count($this->images);
        // Place a unit image via the cm matrix; PDF y is the image's bottom edge.
        $this->content .= sprintf(
            "q\n%.2f 0 0 %.2f %.2f %.2f cm\n/Im%d Do\nQ\n",
            $wPt, $hPt, $xLeft, $this->h - $yTop - $hPt, $n
        );
        return true;
    }

    // Parse width/height/color components from a JPEG's SOF marker. Returns null if not a JPEG.
    private function jpegInfo(string $d): ?array
    {
        $len = strlen($d);
        if ($len < 4 || ord($d[0]) !== 0xFF || ord($d[1]) !== 0xD8) {
            return null; // not JPEG (no SOI)
        }
        $i = 2;
        while ($i + 9 < $len) {
            if (ord($d[$i]) !== 0xFF) { $i++; continue; }
            $marker = ord($d[$i + 1]);
            // SOF markers carry the frame header (skip the arithmetic/other non-baseline ones).
            if ($marker >= 0xC0 && $marker <= 0xCF && !in_array($marker, [0xC4, 0xC8, 0xCC], true)) {
                $h = (ord($d[$i + 5]) << 8) + ord($d[$i + 6]);
                $w = (ord($d[$i + 7]) << 8) + ord($d[$i + 8]);
                $components = ord($d[$i + 9]);
                $cs = $components === 1 ? 'DeviceGray' : ($components === 4 ? 'DeviceCMYK' : 'DeviceRGB');
                return ['w' => $w, 'h' => $h, 'cs' => $cs];
            }
            $segLen = (ord($d[$i + 2]) << 8) + ord($d[$i + 3]);
            if ($segLen < 2) { return null; }
            $i += 2 + $segLen;
        }
        return null;
    }

    private function esc(string $s): string
    {
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($t !== false) { $s = $t; }
        }
        $s = preg_replace('/[^\x20-\x7E]/', '?', $s);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    public function output(): string
    {
        // Fixed objects 1..6; image XObjects follow as 7, 8, ...
        $imgFirstObj = 7;
        $xobjectRefs = '';
        foreach ($this->images as $k => $img) {
            $objNum = $imgFirstObj + $k;
            $xobjectRefs .= sprintf('/Im%d %d 0 R', $k + 1, $objNum);
        }
        $resources = '/Font<</F1 5 0 R/F2 6 0 R>>' . ($xobjectRefs !== '' ? '/XObject<<' . $xobjectRefs . '>>' : '');

        $objs = [
            1 => '<</Type/Catalog/Pages 2 0 R>>',
            2 => '<</Type/Pages/Kids[3 0 R]/Count 1>>',
            3 => sprintf('<</Type/Page/Parent 2 0 R/MediaBox[0 0 %.2f %.2f]/Resources<<%s>>/Contents 4 0 R>>', $this->w, $this->h, $resources),
            4 => '<</Length ' . strlen($this->content) . ">>\nstream\n" . $this->content . 'endstream',
            5 => '<</Type/Font/Subtype/Type1/BaseFont/Helvetica/Encoding/WinAnsiEncoding>>',
            6 => '<</Type/Font/Subtype/Type1/BaseFont/Helvetica-Bold/Encoding/WinAnsiEncoding>>',
        ];
        foreach ($this->images as $k => $img) {
            $objs[$imgFirstObj + $k] = sprintf(
                "<</Type/XObject/Subtype/Image/Width %d/Height %d/ColorSpace/%s/BitsPerComponent 8/Filter/DCTDecode/Length %d>>\nstream\n%s\nendstream",
                $img['w'], $img['h'], $img['cs'], strlen($img['data']), $img['data']
            );
        }

        $count = count($objs);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= $count; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= "$i 0 obj\n" . $objs[$i] . "\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . ($count + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<</Size " . ($count + 1) . "/Root 1 0 R>>\nstartxref\n$xrefPos\n%%EOF";
        return $pdf;
    }
}
