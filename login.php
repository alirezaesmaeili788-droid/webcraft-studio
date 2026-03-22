<?php
session_start();
require "db.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $pw    = $_POST["password"] ?? "";

    if ($email === "" || $pw === "") {
        $errors[] = "Bitte E-Mail und Passwort eingeben.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($pw, $user["password_hash"])) {
            $errors[] = "E-Mail oder Passwort ist falsch.";
        } else {
            session_regenerate_id(true);
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["user_name"] = $user["first_name"] . " " . $user["last_name"];
            $_SESSION["user_email"] = $user["email"];
            // Rollen-System
            $_SESSION["role"] = $user["role"] ?? "student";

            header("Location: account.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>
<body>

  <!-- Navbar (dein Code) -->
  <div class="nav-back fixed-top"></div>
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand me-auto" href="http://localhost/web/index.php">StudySpot</a>

      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
        <div class="offcanvas-header" style="background-color:#186753ff;">
          <h5 class="offcanvas-title">
            <a class="navbar-brand me-auto" href="http://localhost/web/index.php">StudySpot</a>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body" style="background-color: var(--primary);">
          <ul class="navbar-nav justify-content-center flex-grow-1 pe-3">
            <li class="nav-item"><a class="nav-link mx-lg-5" href="http://localhost/web/index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link mx-lg-5" href="http://localhost/web/spots.php">Spots</a></li>
            <li class="nav-item"><a class="nav-link mx-lg-5" href="http://localhost/web/kontakt.php">Kontakt</a></li>
          </ul>
        </div>
      </div>

      <a href="register.php" class="login-button">Registrieren</a>

      <button class="navbar-toggler pe-0" type="button"
              data-bs-toggle="offcanvas"
              data-bs-target="#offcanvasNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
  <!-- End Navbar -->

  <main class="page-space">
    <section class="wrap">
      <div class="card">

        <div class="left">
          <span class="badge">🔐 Login</span>
          <h1>Willkommen!</h1>
          <p>Schön, dass du da bist. Bitte melde dich an.</p>
        </div>

        <div class="form">
          <h2>Anmelden</h2>
          <p class="sub">Mit deinem StudySpot Konto.</p>

          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="">
            <label for="email">E-Mail Adresse</label>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">

            <label for="pw">Passwort</label>
            <input id="pw" name="password" type="password" required>

            <button class="btn btn-primary" type="submit">Login</button>

            <p class="foot">Noch kein Mitglied? <a href="register.php">Registrieren</a></p>
          </form>

        </div>

      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
