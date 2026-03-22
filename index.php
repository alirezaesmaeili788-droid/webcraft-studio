<?php
session_start();
require "db.php";

$role = $_SESSION['role'] ?? 'guest';
$is_logged_in = isset($_SESSION['user_id']);

$topSpot = null;
if ($is_logged_in && !in_array($role, ['owner','admin'], true)) {
    $stmt = $conn->prepare("
        SELECT s.id, s.name, s.type, s.city, s.image_url,
               ROUND(AVG(r.rating),1) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM spots s
        JOIN reviews r ON r.spot_id = s.id
        GROUP BY s.id
        HAVING COUNT(r.id) >= 1
        ORDER BY avg_rating DESC, review_count DESC
        LIMIT 1
    ");
    $stmt->execute();
    $topSpot = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>StudySpot | Home</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link href="style.css" rel="stylesheet">
</head>

<body class="Homepage-body">

    <!-- Navbar -->
    <?php include "navbar.php"; ?>
    <!-- End Navbar -->

    <!-- HERO SECTION -->
    <section class="py-5 hero-wrapper">
        <div class="container">
            <div class="hero-card rounded-4 p-4 p-md-5">

                <div class="row gy-4 align-items-center">

                    <!-- LINKS: Suche / Text -->
                    <div class="col-12 col-lg-6">
                        <div class="cafe-box bg-white rounded-4 mt-5 shadow-sm p-0">
                            <img src="images/cafe-find.png" alt="Lernort finden" class="img-fluid mb-0 hero-image-c">

                            <div class="cafe-s-box p-4 shadow-sm">
                                <h1 class="fw-bold mb-3">Wir machen Lernen einfacher.</h1>

                                <p class="lead mb-4">
                                    StudySpot hilft Schülern und Studierenden, passende Lernorte
                                    zu finden – Cafés, Bibliotheken und ruhige Plätze in der Nähe.
                                    Ohne lange Suche, ohne Stress.
                                </p>

                                <!-- ✅ Suche funktioniert jetzt wirklich -->
                                <form class="hero-search" method="get" action="spots.php">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="q"
                                            placeholder="Bezirk oder Stadtteil eingeben…">
                                        <button class="btn btn-success" type="submit">
                                            Suchen
                                        </button>
                                    </div>

                                    <a href="spots.php" class="btn btn-outline-success w-100 Spotsbtn"
                                        style="border-radius: 14px;">
                                        Alle Spots anzeigen
                                    </a>

                                    <!-- ✅ Guest CTA -->
                                    <?php if (!$is_logged_in): ?>
                                        <div class="d-flex gap-2 mt-3">
                                            <a href="login.php" class="btn btn-success w-50" style="border-radius: 14px;">
                                                Login
                                            </a>
                                            <a href="register.php" class="btn btn-outline-success w-50" style="border-radius: 14px;">
                                                Registrieren
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

<!-- RECHTS: Rolle abhängig -->
<div class="col-12 col-lg-6 d-flex flex-column h-100">
    <div class="d-flex flex-column gap-3 h-100">

    <?php if ($role === 'owner' || $role === 'admin'): ?>
        <!-- ✅ OWNER / ADMIN -->
        <div class="cafe-box bg-white rounded-4 mt-5 shadow-sm p-0">
            <img src="images/cafe-reg.png" alt="Café anmelden" class="img-fluid mb-0 hero-image-c">

            <div class="cafe-info-box p-4 shadow-sm">
                <div class="d-flex align-items-center mb-3">
                    <div class="cafe-icon me-3">🏪</div>
                    <h4 class="fw-bold mb-0">Für Cafébesitzer</h4>
                </div>

                <p class="small text-muted mb-3">
                    Verwalte deine Orte oder melde neue Lernorte an.
                </p>

                <div class="d-grid gap-2">
                    <a href="ort_anmelden.php" class="btn btn-outline-success" style="border-radius:14px;">
                        Café anmelden
                    </a>
                    <a href="owner_home.php" class="btn btn-success" style="border-radius:14px;">
                        Meine Orte
                    </a>
                </div>
            </div>
        </div>
<?php elseif ($is_logged_in): ?>
    <!-- ✅ USER / STUDENT → 2 kleine Boxen (About + Beste Bewertung) -->

    <!-- Box 1: About Us -->
<div class="cafe-box bg-white rounded-4 shadow-sm p-0 flex-fill">
        <div class="cafe-info-box p-4 shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="cafe-icon me-3">ℹ️</div>
                <h4 class="fw-bold mb-0">Über StudySpot</h4>
            </div>

            <p class="small text-muted mb-0">
                StudySpot hilft dir, die besten Lernorte in Wien zu finden –
                mit WLAN, Steckdosen, Ruhe-Level und echten Bewertungen.
            </p>

            <a href="spots.php" class="btn btn-outline-success w-100 mt-3" style="border-radius:14px;">
                Spots entdecken
            </a>
        </div>
    </div>

    <!-- Box 2: Beste Bewertung -->
    <div class="cafe-box bg-white rounded-4 mt-3 shadow-sm p-0">
        <div class="cafe-info-box p-4 shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="cafe-icon me-3">⭐</div>
                <h4 class="fw-bold mb-0">Beste Bewertung</h4>
            </div>

            <?php if (!empty($topSpot)): ?>
                <div class="d-flex gap-3 align-items-center">
                    <?php if (!empty($topSpot['image_url'])): ?>
                        <img src="<?= htmlspecialchars($topSpot['image_url']) ?>"
                             alt=""
                             class="img-fluid"
                             style="width:76px;height:76px;object-fit:cover;border-radius:14px;">
                    <?php else: ?>
                        <div style="width:76px;height:76px;border-radius:14px;background:#e9f7f1;"></div>
                    <?php endif; ?>

                    <div style="min-width:0;">
                        <div class="fw-bold" style="line-height:1.2;">
                            <?= htmlspecialchars($topSpot['name']) ?>
                        </div>
                        <div class="small text-muted">
                            <?= htmlspecialchars($topSpot['type']) ?> · <?= htmlspecialchars($topSpot['city']) ?>
                        </div>
                        <div class="small mt-1">
                            <span class="fw-semibold text-success">
                                ⭐ <?= htmlspecialchars((string)$topSpot['avg_rating']) ?>/5
                            </span>
                            <span class="text-muted">(<?= (int)$topSpot['review_count'] ?>)</span>
                        </div>
                    </div>
                </div>

                <a href="spot.php?id=<?= (int)$topSpot['id'] ?>"
                   class="btn btn-success w-100 mt-3"
                   style="border-radius:14px;">
                    Zum Spot
                </a>
            <?php else: ?>
                <p class="small text-muted mb-0">
                    Noch keine Bewertungen vorhanden. Sei der Erste ⭐
                </p>
                <a href="spots.php" class="btn btn-outline-success w-100 mt-3" style="border-radius:14px;">
                    Spots ansehen
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
        <!-- ✅ GAST -->
        <div class="cafe-box bg-white rounded-4 mt-5 shadow-sm p-0">
            <img src="images/cafe-reg.png" alt="Café anmelden" class="img-fluid mb-0 hero-image-c">

            <div class="cafe-info-box p-4 shadow-sm">
                <div class="d-flex align-items-center mb-3">
                    <div class="cafe-icon me-3">🏪</div>
                    <h4 class="fw-bold mb-0">Für Cafébesitzer</h4>
                </div>

                <p class="small text-muted mb-3">
                    Mach dein Café sichtbar für Studierende.
                </p>

                <a href="register.php"
                   class="btn btn-outline-success w-100"
                   style="border-radius:14px;">
                    Café anmelden
                </a>
            </div>
        </div>
    <?php endif; ?>
    </div>  
</div>


                </div>
            </div>
        </div>
    </section>
    <!-- /HERO SECTION -->

<?php if (!$is_logged_in): ?>
    <!-- ABOUT SECTION (nur Gäste) -->
    <section class="about-section py-2 mb-4">
        <div class="container">
            <div class="row g-4 rounded-4 mt-0 mb-4 shadow-sm p-4 about-d">

                <div class="col-12 text-center">
                    <h2 class="fw-bold mb-3">Über StudySpot</h2>
                    <p class="text-muted mb-4">
                        StudySpot bündelt alle wichtigen Infos zu Lernorten in Wien:
                        WLAN, Lautstärke, Steckdosen, Preise und Bewertungen.
                        So findest du schnell den Spot, der zu dir passt.
                    </p>
                </div>

                <div class="col-12">
                    <div class="row g-3">

                        <div class="col-12 col-md-4">
                            <div class="about-card h-100 p-3 rounded-4 shadow-sm">
                                <div class="about-icon">📍</div>
                                <h5 class="fw-semibold mb-1">Spots finden</h5>
                                <p class="small text-muted mb-0">
                                    Suche nach Bezirk, Ortstyp und Ausstattung.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="about-card h-100 p-3 rounded-4 shadow-sm">
                                <div class="about-icon">⭐</div>
                                <h5 class="fw-semibold mb-1">Bewerten</h5>
                                <p class="small text-muted mb-0">
                                    Teile deine Erfahrungen und hilf anderen Lernenden.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="about-card h-100 p-3 rounded-4 shadow-sm">
                                <div class="about-icon">👥</div>
                                <h5 class="fw-semibold mb-1">Gemeinsam lernen</h5>
                                <p class="small text-muted mb-0">
                                    Speichere Favoriten und plane Sessions mit Freunden.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
<?php endif; ?>

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

    <!-- Bootstrap JS (am Ende ist besser) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

</body>
</html>
