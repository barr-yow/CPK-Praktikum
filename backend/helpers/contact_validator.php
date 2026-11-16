<?php

declare(strict_types=1);

require_once __DIR__ . '/response.php';

/**
 * Validate and normalize contact payload.
 *
 * @param array<string, mixed> $input
 * @return array{name: string, phone: string, email: string, notes: string}
 */
function validateContactPayload(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $phone = trim((string) ($input['phone'] ?? ''));
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $notes = trim((string) ($input['notes'] ?? ''));

    if ($name === '') {
        jsonResponse(false, 'Nama kontak wajib diisi.', null, 422);
    }

    if (mb_strlen($name) > 100) {
        jsonResponse(false, 'Nama kontak maksimal 100 karakter.', null, 422);
    }

    if ($phone === '' && $email === '') {
        jsonResponse(false, 'Isi minimal nomor telepon atau email kontak.', null, 422);
    }

    if ($phone !== '' && (mb_strlen($phone) > 30 || preg_match('/^[0-9+\-\s()]{5,30}$/', $phone) !== 1)) {
        jsonResponse(false, 'Format nomor telepon tidak valid (5-30 karakter).', null, 422);
    }

    if ($email !== '' && (mb_strlen($email) > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
        jsonResponse(false, 'Format email kontak tidak valid.', null, 422);
    }

    if (mb_strlen($notes) > 255) {
        jsonResponse(false, 'Catatan maksimal 255 karakter.', null, 422);
    }

    return [
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'notes' => $notes,
    ];
}
