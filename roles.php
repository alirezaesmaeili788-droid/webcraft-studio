<?php
// roles.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function current_role(): string {
  return $_SESSION['role'] ?? 'student';
}

function require_login(): void {
  if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
}


function require_role(string ...$roles): void {
  require_login();
  $role = current_role();
  if (!in_array($role, $roles, true)) {
    http_response_code(403);
    echo '403 - Kein Zugriff';
    exit;
  }
}


function can_manage_spot($spot_created_by): bool {
  $role = current_role();
  $uid  = (int)($_SESSION['user_id'] ?? 0);
  if ($role === 'admin') return true;
  if ($role === 'owner' && (int)$spot_created_by === $uid) return true;
  return false;
}
