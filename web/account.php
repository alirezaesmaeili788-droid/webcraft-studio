<?php
require "auth.php";     
require "db.php";       

$userId = (int)$_SESSION["user_id"];

$stmt = $conn->prepare("SELECT first_name, last_name, email, created_at, password_hash FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$u = $res->fetch_assoc();
$stmt->close();

$name = trim(($u["first_name"] ?? "") . " " . ($u["last_name"] ?? ""));
$email = $u["email"] ?? ($_SESSION["user_email"] ?? "");
$created = $u["created_at"] ?? null;
$hasPassword = (($u["password_hash"] ?? "") !== "");

$err = $_GET["err"] ?? "";
$ok  = $_GET["ok"] ?? "";
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – Mein Account</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>
<body>

<?php include "navbar.php"; ?>

<main class="page-space">
  <section class="wrap">
    <div class="card">

      <!-- LEFT -->
      <div class="left">
        <span class="badge">👤 Mein Account</span>
        <h1>Hallo, <?= htmlspecialchars($name ?: ($_SESSION["user_name"] ?? "User")) ?>!</h1>
        <p>Du bist eingeloggt. Hier kannst du dein Konto verwalten.</p>

        <div class="mt-3" style="color: var(--muted);">
          <div><strong>E-Mail:</strong> <?= htmlspecialchars($email) ?></div>
          <?php if ($created): ?>
            <div><strong>Seit:</strong> <?= htmlspecialchars(substr($created, 0, 10)) ?></div>
          <?php endif; ?>
        </div>

        <div class="mt-4 d-grid gap-2">
          <a class="btn btn-primary" href="spots.php">Zu den Spots</a>
          <a class="btn btn-outline" href="logout.php">Logout</a>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="form">
        <h2>Account Einstellungen</h2>
        <p class="sub">Passwort ändern und Sicherheit.</p>

        <?php if ($err): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <?php if ($ok === "pw"): ?>
          <div class="alert alert-success">Passwort wurde erfolgreich geändert.</div>
        <?php endif; ?>

        <div class="mini-card">
          <h3 class="mini-title">Passwort ändern</h3>

          <?php if (!$hasPassword): ?>
            <div class="alert alert-info">
              Dein Konto hat aktuell kein Passwort (z.B. Social-Login/Import).
              Du kannst jetzt ein Passwort setzen.
            </div>
          <?php endif; ?>

          <form method="post" action="password_update.php">
            <?php if ($hasPassword): ?>
              <label for="old_password">Altes Passwort</label>
              <input id="old_password" name="old_password" type="password" placeholder="Altes Passwort" required>
            <?php endif; ?>

            <label for="new_password">Neues Passwort</label>
            <input id="new_password" name="new_password" type="password" placeholder="Mindestens 8 Zeichen" required>

            <label for="new_password2">Neues Passwort bestätigen</label>
            <input id="new_password2" name="new_password2" type="password" placeholder="Wiederholen" required>

            <button class="btn btn-primary" type="submit">Passwort speichern</button>
          </form>
        </div>

        <div class="mini-card" style="margin-top:14px;">
          <h3 class="mini-title">Sicherheit</h3>
          <p style="margin:0; color:var(--muted); font-size:14px;">
            Tipp: Verwende ein langes Passwort (12+ Zeichen) und mische Buchstaben, Zahlen und Symbole.
          </p>
        </div>

      </div>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
