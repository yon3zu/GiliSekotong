<?php

if (! function_exists('rupiah')) {
    function rupiah(int|float|null $amount): string
    {
        return 'Rp'.number_format((int) $amount, 0, ',', '.');
    }
}

if (! function_exists('rb')) {
    /** Format ringkas: 350000 → 350rb */
    function rb(int $amount): string
    {
        return $amount % 1000 === 0 ? number_format($amount / 1000, 0, ',', '.').'rb' : rupiah($amount);
    }
}
