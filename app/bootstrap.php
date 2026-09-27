<?php
declare(strict_types=1);
session_start();
spl_autoload_register(function ($class) {
  $prefix = 'App\\';
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  $rel = substr($class, strlen($prefix));
  $path = __DIR__ . '/' . str_replace('\\', '/', $rel) . '.php';
  if (file_exists($path)) require $path;
});
function json_response($data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}
