<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

handleCors();
requireMethod('POST');

$input = getJsonInput();
$name = trim((string) ($input['name'] ?? ''));
$username = trim((string) ($input['username'] ?? ''));
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');

if ($name === '' || $username === '' || $email === '' || $password === '') {
    jsonResponse(false, 'Semua field (name, username, email, password) wajib diisi.', null, 422);
}

if (mb_strlen($name) > 100) {
    jsonResponse(false, 'Nama maksimal 100 karakter.', null, 422);
}

if (preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username) !== 1) {
    jsonResponse(false, 'Username harus 3-50 karakter dan hanya boleh huruf, angka, atau underscore.', null, 422);
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    jsonResponse(false, 'Format email tidak valid.', null, 422);
}

if (strlen($password) < 6) {
    jsonResponse(false, 'Password minimal 6 karakter.', null, 422);
}

$pdo = getDbConnection();

$checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
$checkStmt->execute([
    ':username' => $username,
    ':email' => $email,
]);

if ($checkStmt->fetch() !== false) {
    jsonResponse(false, 'Username atau email sudah terdaftar.', null, 409);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertStmt = $pdo->prepare(
    'INSERT INTO users (name, username, email, password_hash) VALUES (:name, :username, :email, :password_hash)'
);
$insertStmt->execute([
    ':name' => $name,
    ':username' => $username,
    ':email' => $email,
    ':password_hash' => $passwordHash,
]);

$userId = (int) $pdo->lastInsertId();
$token = generateAuthToken($userId, $username);

jsonResponse(true, 'Registrasi berhasil.', [
    'token' => $token,
    'user' => [
        'id' => $userId,
        'name' => $name,
        'username' => $username,
        'email' => $email,
    ],
], 201);
