<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

handleCors();
requireMethod('GET');

$pdo = getDbConnection();
$authUser = requireAuth($pdo);

$query = trim((string) ($_GET['q'] ?? ''));

if ($query !== '') {
    $like = '%' . $query . '%';
    $stmt = $pdo->prepare(
        'SELECT id, name, phone, email, notes, created_at, updated_at
         FROM contacts
         WHERE user_id = :user_id
           AND (name LIKE :q_name OR phone LIKE :q_phone OR email LIKE :q_email)
         ORDER BY name ASC, id DESC'
    );
    $stmt->execute([
        ':user_id' => (int) $authUser['id'],
        ':q_name' => $like,
        ':q_phone' => $like,
        ':q_email' => $like,
    ]);
} else {
    $stmt = $pdo->prepare(
        'SELECT id, name, phone, email, notes, created_at, updated_at
         FROM contacts
         WHERE user_id = :user_id
         ORDER BY name ASC, id DESC'
    );
    $stmt->execute([':user_id' => (int) $authUser['id']]);
}

$contacts = $stmt->fetchAll();

jsonResponse(true, 'Daftar kontak berhasil diambil.', [
    'contacts' => $contacts,
], 200);
