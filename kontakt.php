<?php
session_start();
require "db.php";

$errors = [];
$success = "";

$name = "";
$email = "";
$subject = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $subject === "" || $message === "") {
        $errors[] = "Bitte alle Felder ausfüllen.";
    }
    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Bitte eine gültige E-Mail eingeben.";
    }

    if (!$errors) {
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        $stmt->execute();
        $stmt->close();

        $success = "Danke! Deine Nachricht wurde gesendet.";
        $name = $email = $subject = $message = "";
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>StudySpot | Kontakt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>
<body class="kontakt-body">

    <!-- Navbar -->
    <?php include "navbar.php"; ?>
    <!-- End Navbar -->

<main class="mt-5 pt-4">

    <!-- HEADER -->
    <section class="contact-header py-5 text-center">
        <div class="container">
            <h1 class="fw-bold text-success">Kontakt</h1>
            <p class="text-muted mb-0">
                Wir freuen uns über deine Nachricht!
                Egal ob Feedback, Fragen oder Vorschläge – wir sind für dich da.
            </p>
        </div>
    </section>

    <!-- FORMULAR -->
    <section class="py-4">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-12 col-md-8 col-lg-6">
                    <div class="contact-card p-4 rounded-4 shadow-sm bg-white">

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="kontakt.php">
                            <div class="mb-3">
                                <label class="form-label">Dein Name</label>
                                <input type="text" class="form-control" name="name"
                                       placeholder="Vorname Nachname"
                                       value="<?= htmlspecialchars($name) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">E-Mail Adresse</label>
                                <input type="email" class="form-control" name="email"
                                       placeholder="beispiel@mail.com"
                                       value="<?= htmlspecialchars($email) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Betreff</label>
                                <input type="text" class="form-control" name="subject"
                                       placeholder="Worum geht es?"
                                       value="<?= htmlspecialchars($subject) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nachricht</label>
                                <textarea class="form-control" name="message" rows="5"
                                          placeholder="Schreibe deine Nachricht…" required><?= htmlspecialchars($message) ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                Nachricht senden
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<!-- Footer -->
<footer class="footer mt-5 py-4">
    <div class="container">
        <div class="row g-4">

            <div class="col-12 col-md-4">
                <h5 class="fw-bold mb-2">StudySpot</h5>
                <p class="small text-muted">
                    Finde die besten Lernorte in Wien – Cafés, Bibliotheken und mehr.
                </p>
            </div>

            <div class="col-6 col-md-4">
                <h6 class="fw-semibold mb-2">Links</h6>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="spots.php">Spots</a></li>
                    <li><a href="kontakt.php">Kontakt</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-4">
                <h6 class="fw-semibold mb-2">Kontakt</h6>
                <ul class="footer-links">
                    <li>Email: info@studyspot.at</li>
                    <li>Wien, Österreich</li>
                </ul>
            </div>

        </div>

        <hr class="mt-4">

        <p class="text-center small text-muted mb-0">
            © 2025 StudySpot — Alle Rechte vorbehalten.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
