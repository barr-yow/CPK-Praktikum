<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/contact_validator.php';

handleCors();
requireMethod('POST');

$pdo = getDbConnection();
$authUser = requireAuth($pdo);

$input = getJsonInput();
$data = validateContactPayload($input);

$stmt = $pdo->prepare(
    'INSERT INTO contacts (user_id, name, phone, email, notes)
     VALUES (:user_id, :name, :phone, :email, :notes)'
);
$stmt->execute([
    ':user_id' => (int) $authUser['id'],
    ':name' => $data['name'],
    ':phone' => $data['phone'],
    ':email' => $data['email'],
    ':notes' => $data['notes'],
]);

$contactId = (int) $pdo->lastInsertId();

$fetchStmt = $pdo->prepare(
    'SELECT id, name, phone, email, notes, created_at, updated_at FROM contacts WHERE id = :id LIMIT 1'
);
$fetchStmt->execute([':id' => $contactId]);
$contact = $fetchStmt->fetch();

jsonResponse(true, 'Kontak berhasil ditambahkan.', [
    'contact' => $contact,
], 201);
