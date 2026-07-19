<?php

namespace App\Helpers;

/**
 * Convert angka (rupiah) ke kata-kata Indonesia.
 * Contoh: 1.250.000 → "satu juta dua ratus lima puluh ribu"
 */
class Terbilang
{
    public static function make(int|float $number): string
    {
        $number = (int) abs($number);

        if ($number === 0) {
            return 'nol';
        }

        return trim(self::convert($number));
    }

    private static function convert(int $n): string
    {
        $units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($n < 12) {
            return $units[$n];
        }
        if ($n < 20) {
            return $units[$n - 10] . ' belas';
        }
        if ($n < 100) {
            return $units[intdiv($n, 10)] . ' puluh ' . $units[$n % 10];
        }
        if ($n < 200) {
            return 'seratus ' . self::convert($n - 100);
        }
        if ($n < 1000) {
            return $units[intdiv($n, 100)] . ' ratus ' . self::convert($n % 100);
        }
        if ($n < 2000) {
            return 'seribu ' . self::convert($n - 1000);
        }
        if ($n < 1_000_000) {
            return self::convert(intdiv($n, 1000)) . ' ribu ' . self::convert($n % 1000);
        }
        if ($n < 1_000_000_000) {
            return self::convert(intdiv($n, 1_000_000)) . ' juta ' . self::convert($n % 1_000_000);
        }
        if ($n < 1_000_000_000_000) {
            return self::convert(intdiv($n, 1_000_000_000)) . ' miliar ' . self::convert($n % 1_000_000_000);
        }
        return self::convert(intdiv($n, 1_000_000_000_000)) . ' triliun ' . self::convert($n % 1_000_000_000_000);
    }
}
