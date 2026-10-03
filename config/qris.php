<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QRIS (Placeholder Gateway)
    |--------------------------------------------------------------------------
    |
    | Aplikasi berjalan dengan payment gateway QRIS berbasis placeholder.
    | Isi QRIS_MERCHANT_PAYLOAD dengan string payload QRIS statis milik
    | warung Anda (biasanya didapat dari bank / payment gateway).
    | Nantinya payload ini akan digabungkan dengan nominal & referensi
    | transaksi saat QR dibuat.
    |
    */

    'merchant_payload' => env('QRIS_MERCHANT_PAYLOAD', '00020101021126670016COM.NOBUBANK.WWW0118936009143200000000020215000000000000030303UMI51440014ID.CO.QRIS.WWW0215ID10200181777780303UMI5204581253033605802ID5909WARUNG OS6007JAKARTA6105121106304XXXX'),

    /*
    | Secret key untuk verifikasi signature webhook (HMAC-SHA256).
    | Kosongkan jika gateway placeholder tidak mengirim signature.
    */
    'webhook_secret' => env('QRIS_WEBHOOK_SECRET', 'warungos-placeholder-secret'),

    /*
    | Masa berlaku pembayaran QRIS dalam menit sebelum transaksi kedaluwarsa.
    */
    'expiry_minutes' => (int) env('QRIS_EXPIRY_MINUTES', 15),

    /*
    | Ambang batas stok menipis (notifikasi di dashboard & inventaris).
    */
    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

    /*
    | Endpoint pembayaran gateway (digunakan untuk ping status opsional).
    | Kosongkan karena gateway masih placeholder.
    */
    'endpoint' => env('QRIS_ENDPOINT'),

];
