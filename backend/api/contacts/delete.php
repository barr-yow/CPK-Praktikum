<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

handleCors();
requireMethod(['DELETE', 'POST']);

$pdo = getDbConnection();
$authUser = requireAuth($pdo);

$input = getJsonInput();
$contactId = (int) ($input['id'] ?? $_GET['id'] ?? 0);

if ($contactId <= 0) {
    jsonResponse(false, 'ID kontak tidak valid.', null, 422);
}

$stmt = $pdo->prepare('DELETE FROM contacts WHERE id = :id AND user_id = :user_id');
$stmt->execute([
    ':id' => $contactId,
    ':user_id' => (int) $authUser['id'],
]);

if ($stmt->rowCount() === 0) {
    jsonResponse(false, 'Kontak tidak ditemukan.', null, 404);
}

jsonResponse(true, 'Kontak berhasil dihapus.', null, 200);
