<?php

namespace App\Helpers;

class InvoiceHelper
{
    /**
     * Convert number to Indonesian words (Terbilang)
     *
     * @param  float|int  $number
     * @return string
     */
    public static function terbilang(float|int $number): string
    {
        $number = (int) round($number);
        
        if ($number === 0) {
            return 'Nol';
        }
        
        $units = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
            'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh',
            'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
            'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'
        ];
        
        $tens = [
            '', '', 'Dua Puluh', 'Tiga Puluh', 'Empat Puluh',
            'Lima Puluh', 'Enam Puluh', 'Tujuh Puluh', 'Delapan Puluh', 'Sembilan Puluh'
        ];
        
        $scales = [
            '', 'Ribu', 'Juta', 'Miliar', 'Triliun'
        ];
        
        $result = [];
        $scaleIndex = 0;
        
        while ($number > 0) {
            $group = $number % 1000;
            
            if ($group > 0) {
                $groupWords = [];
                
                $hundreds = (int) ($group / 100);
                $remainder = $group % 100;
                
                if ($hundreds > 0) {
                    if ($hundreds === 1) {
                        $groupWords[] = 'Seratus';
                    } else {
                        $groupWords[] = $units[$hundreds] . ' Ratus';
                    }
                }
                
                if ($remainder > 0) {
                    if ($remainder < 20) {
                        $groupWords[] = $units[$remainder];
                    } else {
                        $ten = (int) ($remainder / 10);
                        $unit = $remainder % 10;
                        
                        $groupWords[] = $tens[$ten];
                        if ($unit > 0) {
                            $groupWords[] = $units[$unit];
                        }
                    }
                }
                
                if ($scaleIndex > 0) {
                    if ($group === 1 && $scaleIndex === 1) {
                        // Special case: "Seribu" not "Satu Ribu"
                        $groupWords = ['Seribu'];
                    } else {
                        $groupWords[] = $scales[$scaleIndex];
                    }
                }
                
                array_unshift($result, implode(' ', $groupWords));
            }
            
            $number = (int) ($number / 1000);
            $scaleIndex++;
        }
        
        return implode(' ', $result);
    }
    
    /**
     * Format currency for Indonesian Rupiah
     *
     * @param  float|int  $amount
     * @return string
     */
    public static function formatIDR(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}