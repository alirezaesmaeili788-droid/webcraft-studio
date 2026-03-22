<?php
require "auth.php";      // session_start + login-check
require "db.php";        // $conn

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: account.php");
  exit;
}

$userId = (int)($_SESSION["user_id"] ?? 0);
$oldPw  = $_POST["old_password"] ?? "";
$newPw  = $_POST["new_password"] ?? "";
$newPw2 = $_POST["new_password2"] ?? "";

// User laden
$stmt = $conn->prepare("SELECT password_hash FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
  header("Location: account.php?msg=nouser");
  exit;
}

$currentHash = $user["password_hash"] ?? "";

// Validierung
if (strlen($newPw) < 8) $errors[] = "Neues Passwort muss mindestens 8 Zeichen haben.";
if ($newPw !== $newPw2) $errors[] = "Neue Passwörter stimmen nicht überein.";

// Wenn schon ein Passwort existiert → altes Passwort prüfen
if ($currentHash !== "") {
  if ($oldPw === "") $errors[] = "Bitte altes Passwort eingeben.";
  else if (!password_verify($oldPw, $currentHash)) $errors[] = "Altes Passwort ist falsch.";
}

if ($errors) {
  // Fehler zurückgeben über GET (einfach)
  header("Location: account.php?err=" . urlencode(implode(" | ", $errors)));
  exit;
}

// Update
$newHash = password_hash($newPw, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
$stmt->bind_param("si", $newHash, $userId);
$stmt->execute();
$stmt->close();

header("Location: account.php?ok=pw");
exit;
