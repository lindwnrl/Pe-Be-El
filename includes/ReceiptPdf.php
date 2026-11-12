<?php
declare(strict_types=1);

/**
 * Minimal PDF writer.
 *
 * The project has no Composer dependencies, so receipts are produced by hand
 * with the Type 1 base fonts every PDF reader ships. Layout is absolute: this
 * emits a single A4 page of text and rules, nothing more.
 */
final class ReceiptPdf
{
    private const PAGE_W = 595.28;   // A4, in points
    private const PAGE_H = 841.89;
    private const MARGIN = 48.0;

    /** @var string[] PDF drawing operators for the single content stream */
    private array $ops = [];

    /** Vertical cursor, measured from the top of the page. */
    private float $y;

    public function __construct()
    {
        $this->y = self::MARGIN;
    }

    /** Escape for a PDF literal string and flatten to Latin-1. */
    private function text(string $value): string
    {
        $value = strtr($value, [
            '—' => '-', '–' => '-', '·' => '-', '×' => 'x', '“' => '"', '”' => '"',
            '‘' => "'", '’' => "'", '…' => '...', '✔' => '', '→' => '->',
        ]);

        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
        if ($converted !== false) {
            $value = $converted;
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    /** Rough Helvetica advance width; good enough to right-align amounts. */
    private function width(string $value, float $size): float
    {
        return strlen($value) * $size * 0.5;
    }

    /** Draw a string at an absolute baseline, left or right aligned. */
    private function put(string $value, float $size, bool $bold, float $y, bool $right = false, float $indent = 0): void
    {
        $x = $right
            ? self::PAGE_W - self::MARGIN - $this->width($this->text($value), $size)
            : self::MARGIN + $indent;

        $this->ops[] = sprintf(
            'BT %s %s Tf 1 0 0 1 %s %s Tm (%s) Tj ET',
            $bold ? '/F2' : '/F1',
            $this->num($size),
            $this->num($x),
            $this->num($y),
            $this->text($value)
        );
    }

    private function num(float $value): string
    {
        return rtrim(rtrim(sprintf('%.3F', $value), '0'), '.');
    }

    /** Baseline for the current cursor, accounting for the font size. */
    private function baseline(float $size): float
    {
        return self::PAGE_H - $this->y - $size;
    }

    public function drawText(string $value, float $size = 10, bool $bold = false, float $leading = 15, float $indent = 0): void
    {
        $this->put($value, $size, $bold, $this->baseline($size), false, $indent);
        $this->y += $leading;
    }

    public function drawHeading(string $value, float $size = 20): void
    {
        $this->put($value, $size, true, $this->baseline($size));
        $this->y += $size + 6;
    }

    /** Label on the left, value flush to the right margin, same baseline. */
    public function drawPair(string $label, string $value, float $size = 10, bool $bold = false): void
    {
        $y = $this->baseline($size);
        $this->put($label, $size, false, $y);
        $this->put($value, $size, $bold, $y, true);
        $this->y += 15;
    }

    public function rule(float $weight = 0.7): void
    {
        $y = self::PAGE_H - $this->y;
        $this->ops[] = sprintf(
            '%s w %s %s m %s %s l S',
            $this->num($weight),
            $this->num(self::MARGIN),
            $this->num($y),
            $this->num(self::PAGE_W - self::MARGIN),
            $this->num($y)
        );
        $this->y += 14;
    }

    public function gap(float $points = 10): void
    {
        $this->y += $points;
    }

    public function output(): string
    {
        $content = implode("\n", $this->ops);
        $length = strlen($content);

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %s %s] '
                . '/Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
                $this->num(self::PAGE_W),
                $this->num(self::PAGE_H)
            ),
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            "<< /Length {$length} >>\nstream\n{$content}\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $count = count($objects) + 1;

        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}