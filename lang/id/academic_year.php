<?php

declare(strict_types=1);

return [
    'name' => [
        'required' => 'Nama tahun akademik wajib diisi.',
        'unique' => 'Nama tahun akademik ini sudah ada.',
    ],
    'start_date' => [
        'required' => 'Tanggal mulai wajib diisi.',
        'date' => 'Tanggal mulai harus berupa tanggal yang valid.',
    ],
    'end_date' => [
        'required' => 'Tanggal selesai wajib diisi.',
        'date' => 'Tanggal selesai harus berupa tanggal yang valid.',
        'after' => 'Tanggal selesai harus setelah tanggal mulai.',
    ],
];
