<?php
session_start();
require "db.php";

// --- Spot-ID holen (sehr wichtig: bevor Reviews verarbeitet werden) ---
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { header("Location: spots.php"); exit; }
$spot_id = $id;

$user_id = (int)($_SESSION['user_id'] ?? 0);
$review_error = "";

// --- Review speichern (nur wenn eingeloggt) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_review"])) {
  if ($user_id <= 0) {
    header("Location: login.php");
    exit;
  }

  $rating  = (int)($_POST["rating"] ?? 0);
  $comment = trim($_POST["comment"] ?? "");

  if ($rating < 1 || $rating > 5) {
    $review_error = "Bitte Sterne (1–5) wählen.";
  } else {
    $stmt = $conn->prepare("
      INSERT INTO reviews (spot_id, user_id, rating, comment)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)
    ");
    $stmt->bind_param("iiis", $spot_id, $user_id, $rating, $comment);
    $stmt->execute();
    $stmt->close();

    header("Location: spot.php?id=" . $spot_id);
    exit;
  }
}

// --- Spot laden ---
$stmt = $conn->prepare("SELECT * FROM spots WHERE id=?");
$stmt->bind_param("i", $spot_id);
$stmt->execute();
$res = $stmt->get_result();
$spot = $res->fetch_assoc();
$stmt->close();

if (!$spot) { header("Location: spots.php"); exit; }

// --- Review-Stats (Durchschnitt + Anzahl) ---
$stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE spot_id=?");
$stmt->bind_param("i", $spot_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avg = $stats['avg_rating'] ? round((float)$stats['avg_rating'], 1) : null;
$cnt = (int)($stats['cnt'] ?? 0);

// --- Review-Liste ---
$stmt = $conn->prepare("
  SELECT r.rating, r.comment, r.created_at, u.first_name, u.last_name
  FROM reviews r
  JOIN users u ON u.id = r.user_id
  WHERE r.spot_id=?
  ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $spot_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – <?= htmlspecialchars($spot["name"]) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
  <style>
    .heroimg{height:320px; object-fit:cover; border-radius:20px; box-shadow:var(--shadow);}
    .info{background:#fff; border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow); padding:18px;}
    .tag{background:var(--mint); color:var(--primary-dark); border-radius:999px; padding:6px 10px; font-weight:800; font-size:13px;}
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="page-space">
  <div class="wrap">

    <div class="mt-3">
      <?php if (!empty($spot["image_url"])): ?>
        <img class="w-100 heroimg" src="<?= htmlspecialchars($spot["image_url"]) ?>" alt="">
      <?php endif; ?>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
      <h1 style="margin:0; color:#1F5E3B;"><?= htmlspecialchars($spot["name"]) ?></h1>
      <span class="tag"><?= htmlspecialchars($spot["type"]) ?></span>
    </div>

    <p class="sub" style="margin-top:8px;">
      <?= htmlspecialchars($spot["address"]) ?>, <?= htmlspecialchars($spot["zip"]) ?> <?= htmlspecialchars($spot["city"]) ?>
    </p>

    <!-- Rating summary -->
    <div class="mb-2">
      <?php if ($avg !== null): ?>
        <span class="badge bg-success">⭐ <?= htmlspecialchars((string)$avg) ?> / 5</span>
        <small class="text-muted">(<?= $cnt ?> Bewertungen)</small>
      <?php else: ?>
        <small class="text-muted">Noch keine Bewertungen</small>
      <?php endif; ?>
    </div>

    <div class="row g-3 mt-2">
      <div class="col-lg-8">
        <div class="info">
          <h3 style="margin:0 0 10px 0;">Beschreibung</h3>
          <p style="margin:0; color:var(--text); line-height:1.6;">
            <?= nl2br(htmlspecialchars($spot["description"] ?? "Keine Beschreibung vorhanden.")) ?>
          </p>
        </div>

        <!-- Review Form -->
<div class="info my-4">
  <h3 style="margin:0 0 14px 0;">Bewertung schreiben</h3>

  <?php if (!empty($review_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($review_error) ?></div>
  <?php endif; ?>

  <?php if (empty($_SESSION['user_id'])): ?>
    <div class="alert alert-info mb-0">
      Bitte <a href="login.php">einloggen</a>, um zu bewerten.
    </div>
  <?php else: ?>
    <form method="post" style="max-width:650px;">
      <input type="hidden" name="add_review" value="1">

      <div class="mb-3">
        <label class="form-label" style="font-weight:700;">Sterne</label>
        <select name="rating" class="form-select" required>
          <option value="">Bitte wählen…</option>
          <option value="5">⭐⭐⭐⭐⭐ (5) Top</option>
          <option value="4">⭐⭐⭐⭐ (4) Gut</option>
          <option value="3">⭐⭐⭐ (3) Ok</option>
          <option value="2">⭐⭐ (2) Naja</option>
          <option value="1">⭐ (1) Schlecht</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label" style="font-weight:700;">Kommentar (optional)</label>
        <textarea name="comment" class="form-control" rows="4" placeholder="Wie war es dort?"></textarea>
      </div>

      <div class="d-flex justify-content-end">
        <button class="btn btn-primary px-4" type="submit">Speichern</button>
      </div>
    </form>
  <?php endif; ?>
</div>


        <!-- Reviews List -->
        <div class="mt-4">
          <h5>Bewertungen</h5>

          <?php if (empty($reviews)): ?>
            <p class="text-muted">Noch keine Bewertungen.</p>
          <?php else: ?>
            <?php foreach ($reviews as $r): ?>
              <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between">
                  <strong><?= htmlspecialchars(($r['first_name'] ?? '')." ".($r['last_name'] ?? '')) ?></strong>
                  <small class="text-muted"><?= htmlspecialchars($r['created_at']) ?></small>
                </div>
                <div>⭐ <?= (int)$r['rating'] ?> / 5</div>
                <?php if (!empty($r['comment'])): ?>
                  <div class="mt-2"><?= nl2br(htmlspecialchars($r['comment'])) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>

      <div class="col-lg-4">
        <div class="info">
          <h3 style="margin:0 0 10px 0;">Infos</h3>
          <ul style="margin:0; padding-left:18px; color:var(--muted);">
            <li>WLAN: <?= !empty($spot["wifi"]) ? "Ja" : "Nein" ?></li>
            <li>Steckdosen: <?= !empty($spot["power_outlets"]) ? "Ja" : "Nein" ?></li>
            <li>Lautstärke: <?= htmlspecialchars($spot["quiet_level"] ?? "") ?></li>
            <li>Gruppen: <?= !empty($spot["group_friendly"]) ? "Geeignet" : "Eher nicht" ?></li>
          </ul>
        </div>
      </div>
    </div>

    <a href="spots.php" class="btn btn-outline" style="border-radius:999px;">← Zurück</a>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
