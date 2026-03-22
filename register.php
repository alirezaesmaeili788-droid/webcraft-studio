<?php
session_start();
require "db.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST["first_name"] ?? "");
    $last  = trim($_POST["last_name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $pw    = $_POST["password"] ?? "";
    $pw2   = $_POST["password2"] ?? "";
    $terms = isset($_POST["terms"]);

    $role = 'student';
if (isset($_POST["is_owner"])) {
    $role = 'owner';
}

    if ($first === "" || $last === "" || $email === "" || $pw === "" || $pw2 === "") {
        $errors[] = "Bitte alle Felder ausfüllen.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Bitte eine gültige E-Mail eingeben.";
    }
    if (strlen($pw) < 8) {
        $errors[] = "Passwort muss mindestens 8 Zeichen haben.";
    }
    if ($pw !== $pw2) {
        $errors[] = "Passwörter stimmen nicht überein.";
    }
    if (!$terms) {
        $errors[] = "Bitte Nutzungsbedingungen akzeptieren.";
    }

    // Email schon vorhanden?
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Diese E-Mail ist bereits registriert.";
        }
        $stmt->close();
    }

    // Speichern
    if (!$errors) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
  "INSERT INTO users (first_name, last_name, email, password_hash, role)
   VALUES (?,?,?,?,?)"
);
$stmt->bind_param("sssss", $first, $last, $email, $hash, $role);

        $stmt->execute();
        $stmt->close();

        $success = "Registrierung erfolgreich! Du kannst dich jetzt einloggen.";
    }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – Registrieren</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>
<body>

  <!-- Navbar -->
<?php include "navbar.php"; ?>

  <!-- End Navbar -->

  <main class="page-space">
    <section class="wrap">
      <div class="card">

        <div class="left">
          <span class="badge">🎓 StudySpot</span>
          <h1>Jetzt registrieren</h1>
          <p>Erstelle kostenlos ein Konto und finde ruhige Lernorte in deiner Nähe.</p>
        </div>

        <div class="form">
          <h2>Konto erstellen</h2>
          <p class="sub">Es dauert nur eine Minute.</p>

          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success">
              <?= htmlspecialchars($success) ?>
              <div><a href="login.php">Zum Login</a></div>
            </div>
          <?php endif; ?>

          <form method="post" action="">
            <div class="form-grid-2">
              <div>
                <label for="vorname">Vorname</label>
                <input id="vorname" name="first_name" type="text" required
                       value="<?= htmlspecialchars($_POST["first_name"] ?? "") ?>">
              </div>
              <div>
                <label for="nachname">Nachname</label>
                <input id="nachname" name="last_name" type="text" required
                       value="<?= htmlspecialchars($_POST["last_name"] ?? "") ?>">
              </div>
            </div>

            <label for="email">E-Mail Adresse</label>
            <input id="email" name="email" type="email" required
                   value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">

            <label for="pw">Passwort</label>
            <input id="pw" name="password" type="password" required>

            <label for="pw2">Passwort bestätigen</label>
            <input id="pw2" name="password2" type="password" required>
<!-- OWNER (Switch) -->
<div class="form-check form-switch mb-3">
  <input class="form-check-input" type="checkbox" role="switch" id="is_owner" name="is_owner" value="1">
  <label class="form-check-label fw-semibold" for="is_owner">
    Ich bin Betreiber eines Lernortes (Owner)
  </label>
</div>

<!-- AGB / Datenschutz -->
<div class="form-check mb-3">
  <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
  <label class="form-check-label" for="terms" style="margin-left: 1.5em;">
    Ich akzeptiere die
    <a href="terms.php" target="_blank" rel="noopener">Nutzungsbedingungen</a>
    und die
    <a href="privacy.php" target="_blank" rel="noopener">Datenschutzerklärung</a>.
  </label>
</div>

            <button class="btn btn-primary" type="submit">Registrieren</button>
            <p class="foot">
              Schon ein Konto? <a href="login.php">Jetzt einloggen</a>
            </p>
          </form>

        </div>

      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
