<?php
/**
 * PDO database connection for the Trade system.
 * All scripts should use $pdo (PDO instance) for consistency.
 */
require_once __DIR__ . '/../app/Db/Db.php';

use App\Db\Db;

$pdo = Db::pdo();
