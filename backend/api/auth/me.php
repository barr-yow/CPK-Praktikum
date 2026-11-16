<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

handleCors();
requireMethod('GET');

$pdo = getDbConnection();
$user = requireAuth($pdo);

jsonResponse(true, 'Profil pengguna berhasil diambil.', [
    'user' => [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'email' => $user['email'],
        'created_at' => $user['created_at'],
    ],
], 200);
