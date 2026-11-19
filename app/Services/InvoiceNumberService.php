<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service untuk generate nomor invoice unik dengan format:
 * INV/YYYY/MM/XXXX
 *
 * Menggunakan database transaction + row lock untuk menghindari race condition
 * pada running number bulanan.
 */
class InvoiceNumberService
{
    /**
     * Generate nomor invoice unik berikutnya.
     *
     * @return string Nomor invoice (contoh: INV/2025/01/0001)
     */
    public function generate(): string
    {
        $config = config('invoice.numbering');
        $prefix = $config['prefix'];
        $dateFormat = $config['date_format'];
        $runningDigits = $config['running_digits'];
        $separator = $config['separator'];

        $now = now();
        $datePart = $now->format($dateFormat); // YYYY/MM

        return DB::transaction(function () use ($prefix, $datePart, $runningDigits, $separator) {
            // Lock the row for this month to prevent race conditions
            $counter = DB::table('invoice_counters')
                ->where('year_month', $datePart)
                ->lockForUpdate()
                ->first();

            if ($counter) {
                $nextNumber = $counter->counter + 1;
                DB::table('invoice_counters')
                    ->where('year_month', $datePart)
                    ->update(['counter' => $nextNumber, 'updated_at' => now()]);
            } else {
                $nextNumber = 1;
                DB::table('invoice_counters')->insert([
                    'year_month' => $datePart,
                    'counter'    => $nextNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $runningPart = Str::padLeft((string) $nextNumber, $runningDigits, '0');

            return implode($separator, [$prefix, $datePart, $runningPart]);
        });
    }

    /**
     * Parse nomor invoice untuk mendapatkan komponennya.
     *
     * @return array{prefix: string, year: int, month: int, running: int}
     */
    public function parse(string $invoiceNumber): array
    {
        $config = config('invoice.numbering');
        $separator = $config['separator'];
        $parts = explode($separator, $invoiceNumber);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException("Format nomor invoice tidak valid: {$invoiceNumber}");
        }

        [$prefix, $datePart, $runningPart] = $parts;
        [$year, $month] = explode('/', $datePart);

        return [
            'prefix'  => $prefix,
            'year'    => (int) $year,
            'month'   => (int) $month,
            'running' => (int) $runningPart,
        ];
    }
}