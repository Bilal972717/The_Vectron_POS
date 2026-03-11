<?php

namespace App\Services;

/**
 * EAN-13 barcode auto-generation service.
 *
 * Generates a unique 12-digit number (with check digit = 13 digits total),
 * validates it as a proper EAN-13 check digit, and renders the barcode as SVG
 * using picqer/php-barcode-generator.
 *
 * Usage:
 *   $sku = BarcodeService::generateEan13Sku();          // unique SKU string
 *   $svg = BarcodeService::renderSvg($sku);             // SVG markup
 */
class BarcodeService
{
    /**
     * Generate a unique EAN-13 SKU (13-digit string).
     * Prefix "200" reserved for internal/store use per GS1 spec.
     */
    public static function generateEan13Sku(): string
    {
        do {
            // 200 prefix + 9 random digits + check digit = 13 chars
            $digits = '200' . str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $ean13  = $digits . self::ean13CheckDigit($digits);
        } while (\App\Models\Product::where('sku', $ean13)->exists());

        return $ean13;
    }

    /**
     * Calculate EAN-13 check digit.
     */
    public static function ean13CheckDigit(string $first12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$first12[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Render EAN-13 as inline SVG string.
     * Requires picqer/php-barcode-generator.
     */
    public static function renderSvg(string $ean13): string
    {
        if (!class_exists(\Picqer\Barcode\BarcodeGeneratorSVG::class)) {
            return '';
        }
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return $generator->getBarcode($ean13, \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_EAN_13, 2, 60);
    }

    /**
     * Render EAN-13 as PNG base64 data URI for use in <img src="...">.
     */
    public static function renderPngBase64(string $ean13): string
    {
        if (!class_exists(\Picqer\Barcode\BarcodeGeneratorPNG::class)) {
            return '';
        }
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $png = $generator->getBarcode($ean13, \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_EAN_13, 2, 60);
        return 'data:image/png;base64,' . base64_encode($png);
    }
}
