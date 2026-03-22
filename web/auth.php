<?php
// auth.php
// - erzwingt Login (wie vorher)
// - bietet zusätzlich require_role() für Rollen-System

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!function_exists('require_login')) {
  function require_login(): void {
    if (empty($_SESSION["user_id"])) {
      header("Location: login.php");
      exit;
    }
  }
}

if (!function_exists('require_role')) {
  /**
   * @param string[] $roles z.B. ['admin'] oder ['admin','owner']
   */
  function require_role(array $roles): void {
    require_login();
    $role = $_SESSION['role'] ?? 'student';
    if (!in_array($role, $roles, true)) {
      http_response_code(403);
      echo "403 - Kein Zugriff";
      exit;
    }
  }
}

// Standard: jede Seite, die auth.php einbindet, braucht Login
require_login();
function can_manage_spot($spot_created_by) {
    $role = $_SESSION['role'] ?? 'student';
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    if ($role === 'admin') return true;
    if ($role === 'owner' && (int)$spot_created_by === $user_id) return true;

    return false;
}
