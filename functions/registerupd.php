<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=UTF-8');

// Backwards compatible endpoint for checking uniqueness.
// - POST username=<value> => echoes count (0 if available, >0 if taken)
// - POST email=<value>    => echoes count (0 if available, >0 if in use)
//
// Users live in tblusers.

try {
  if (isset($_POST['username']) && $_POST['username'] !== '') {
    $username = trim($_POST['username']);
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM tblusers WHERE username = ?");
    $stmt->execute([$username]);
    echo (string) ((int) $stmt->fetchColumn());
    exit;
  }

  if (isset($_POST['email']) && $_POST['email'] !== '') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM tblusers WHERE email = ?");
    $stmt->execute([$email]);
    echo (string) ((int) $stmt->fetchColumn());
    exit;
  }

  echo "0";
} catch (Throwable $e) {
  // Keep response simple for JS callers
  echo "0";
}
