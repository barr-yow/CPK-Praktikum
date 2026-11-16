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
$password = (string) ($input['password'] ?? '');

if ($password === '') {
    jsonResponse(false, 'Konfirmasi password wajib diisi untuk menghapus akun.', null, 422);
}

$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => (int) $authUser['id']]);
$row = $stmt->fetch();

if ($row === false || !password_verify($password, (string) $row['password_hash'])) {
    jsonResponse(false, 'Password konfirmasi tidak sesuai.', null, 401);
}

$deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
$deleteStmt->execute([':id' => (int) $authUser['id']]);

jsonResponse(true, 'Akun dan seluruh kontak terkait berhasil dihapus.', null, 200);
