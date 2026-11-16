<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/contact_validator.php';

handleCors();
requireMethod(['PUT', 'POST']);

$pdo = getDbConnection();
$authUser = requireAuth($pdo);

$input = getJsonInput();
$contactId = (int) ($input['id'] ?? $_GET['id'] ?? 0);

if ($contactId <= 0) {
    jsonResponse(false, 'ID kontak tidak valid.', null, 422);
}

$checkStmt = $pdo->prepare('SELECT id FROM contacts WHERE id = :id AND user_id = :user_id LIMIT 1');
$checkStmt->execute([
    ':id' => $contactId,
    ':user_id' => (int) $authUser['id'],
]);

if ($checkStmt->fetch() === false) {
    jsonResponse(false, 'Kontak tidak ditemukan.', null, 404);
}

$data = validateContactPayload($input);

$updateStmt = $pdo->prepare(
    'UPDATE contacts
     SET name = :name, phone = :phone, email = :email, notes = :notes
     WHERE id = :id AND user_id = :user_id'
);
$updateStmt->execute([
    ':name' => $data['name'],
    ':phone' => $data['phone'],
    ':email' => $data['email'],
    ':notes' => $data['notes'],
    ':id' => $contactId,
    ':user_id' => (int) $authUser['id'],
]);

$fetchStmt = $pdo->prepare(
    'SELECT id, name, phone, email, notes, created_at, updated_at FROM contacts WHERE id = :id LIMIT 1'
);
$fetchStmt->execute([':id' => $contactId]);
$contact = $fetchStmt->fetch();

jsonResponse(true, 'Kontak berhasil diperbarui.', [
    'contact' => $contact,
], 200);
