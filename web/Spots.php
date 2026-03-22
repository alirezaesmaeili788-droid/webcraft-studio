<?php
session_start();
require "db.php";

$q = trim($_GET["q"] ?? "");
$type = trim($_GET["type"] ?? "");
$wifi = $_GET["wifi"] ?? "";
$power = $_GET["power_outlets"] ?? "";
$group = $_GET["group_friendly"] ?? "";
$quiet = trim($_GET["quiet_level"] ?? "");

$sql = "
SELECT 
  s.*,
  ROUND(AVG(r.rating),1) AS avg_rating,
  COUNT(r.id) AS review_count
FROM spots s
LEFT JOIN reviews r ON r.spot_id = s.id
WHERE 1=1
";
$params = [];
$types = "";

if ($q !== "") {
  $sql .= " AND (s.name LIKE ? OR s.address LIKE ? OR s.city LIKE ? OR s.zip LIKE ?)";
  $like = "%".$q."%";
  $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "ssss";
}

if ($type !== "" && in_array($type, ["Cafe","Bibliothek","Uni","CoWorking","Sonstiges"], true)) {
  $sql .= " AND s.type = ?";
  $params[] = $type;
  $types .= "s";
}

// WLAN
if ($wifi !== "" && ($wifi === "0" || $wifi === "1")) {
  $sql .= " AND s.wifi = ?";
  $params[] = (int)$wifi;
  $types .= "i";
}

// Steckdosen
if ($power !== "" && ($power === "0" || $power === "1")) {
  $sql .= " AND s.power_outlets = ?";
  $params[] = (int)$power;
  $types .= "i";
}

// Gruppenfreundlich
if ($group !== "" && ($group === "0" || $group === "1")) {
  $sql .= " AND s.group_friendly = ?";
  $params[] = (int)$group;
  $types .= "i";
}

// Ruhe-Level
if ($quiet !== "" && in_array($quiet, ["quiet","medium","loud"], true)) {
  $sql .= " AND s.quiet_level = ?";
  $params[] = $quiet;
  $types .= "s";
}

$sql .= " GROUP BY s.id";
$sql .= " ORDER BY s.created_at DESC"; // <- FIX

$stmt = $conn->prepare($sql);
if (!$stmt) {
  die("SQL Prepare Error: " . htmlspecialchars($conn->error));
}
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$spots = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – Spots</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
  <style>
    .spot-card img{height:200px; object-fit:cover; border-radius:16px;}
    .spot-card{border-radius:20px; box-shadow:var(--shadow); border:1px solid var(--border);}
    .pill{display:inline-block; padding:4px 10px; border-radius:999px; background:var(--mint); color:var(--primary-dark); font-weight:700; font-size:13px;}
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="page-space">
  <div class="wrap">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h1 style="color:#1F5E3B; margin:0;">Spots</h1>
        <p class="sub" style="margin:6px 0 0 0;">Finde Cafés, Bibliotheken und Lernorte.</p>
      </div>
    </div>

    <form class="row g-2 mb-4" method="get" action="">
      <div class="col-md-8">
        <input class="form-control" name="q" placeholder="Bezirk, Adresse oder Name..." value="<?= htmlspecialchars($q) ?>">
      </div>

      <div class="col-md-3">
        <select class="form-select" name="type">
          <option value="">Alle Typen</option>
          <?php foreach (["Cafe","Bibliothek","Uni","CoWorking","Sonstiges"] as $t): ?>
            <option value="<?= $t ?>" <?= $type===$t ? "selected" : "" ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <select class="form-select" name="wifi">
          <option value="">WLAN (egal)</option>
          <option value="1" <?= ($wifi==="1") ? "selected" : "" ?>>WLAN: ja</option>
          <option value="0" <?= ($wifi==="0") ? "selected" : "" ?>>WLAN: nein</option>
        </select>
      </div>

      <div class="col-md-2">
        <select class="form-select" name="power_outlets">
          <option value="">Steckdosen (egal)</option>
          <option value="1" <?= ($power==="1") ? "selected" : "" ?>>Steckdosen: ja</option>
          <option value="0" <?= ($power==="0") ? "selected" : "" ?>>Steckdosen: nein</option>
        </select>
      </div>

      <div class="col-md-2">
        <select class="form-select" name="group_friendly">
          <option value="">Gruppe (egal)</option>
          <option value="1" <?= ($group==="1") ? "selected" : "" ?>>Gruppe: ja</option>
          <option value="0" <?= ($group==="0") ? "selected" : "" ?>>Gruppe: nein</option>
        </select>
      </div>

      <div class="col-md-2">
        <select class="form-select" name="quiet_level">
          <option value="">Ruhe (egal)</option>
          <option value="quiet" <?= ($quiet==="quiet") ? "selected" : "" ?>>ruhig</option>
          <option value="medium" <?= ($quiet==="medium") ? "selected" : "" ?>>mittel</option>
          <option value="loud" <?= ($quiet==="loud") ? "selected" : "" ?>>laut</option>
        </select>
      </div>

      <div class="col-md-1 d-grid">
        <button class="btn btn-outline" type="submit" style="border-radius:999px;">Suchen</button>
      </div>
    </form>

    <div class="row g-4">
      <?php if (!$spots): ?>
        <div class="col-12">
          <div class="alert alert-info">Keine Spots gefunden.</div>
        </div>
      <?php endif; ?>

      <?php foreach ($spots as $s): ?>
        <div class="col-12 col-lg-6">
          <div class="p-3 bg-white spot-card">
            <?php if (!empty($s["image_url"])): ?>
              <img src="<?= htmlspecialchars($s["image_url"]) ?>" class="w-100 mb-3" alt="">
            <?php else: ?>
              <div class="w-100 mb-3" style="height:200px; border-radius:16px; background:var(--mint); display:flex; align-items:center; justify-content:center; color:var(--muted);">
                Kein Bild
              </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-start gap-2">
              <div>
                <h3 style="margin:0;"><?= htmlspecialchars($s["name"]) ?></h3>

                <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                  <div class="pill"><?= htmlspecialchars($s["type"]) ?></div>

                  <!-- ⭐ Reviews (NEU) -->
                  <?php if (!empty($s["review_count"])): ?>
                    <div class="small text-success fw-semibold">
                      ⭐ <?= htmlspecialchars((string)$s["avg_rating"]) ?> <span class="text-muted">(<?= (int)$s["review_count"] ?>)</span>
                    </div>
                  <?php else: ?>
                    <div class="small text-muted">Keine Bewertungen</div>
                  <?php endif; ?>
                </div>

                <p class="sub mt-2 mb-2" style="font-size:14px;">
                  <?= htmlspecialchars($s["address"]) ?>, <?= htmlspecialchars($s["zip"]) ?> <?= htmlspecialchars($s["city"]) ?>
                </p>
              </div>
            </div>

            <p style="color:var(--muted); margin:0 0 10px 0;">
              <?= htmlspecialchars(mb_strimwidth($s["description"] ?? "", 0, 140, "...")) ?>
            </p>

            <a class="btn btn-outline" href="spot.php?id=<?= (int)$s["id"] ?>" style="border-radius:999px;">
              Mehr Infos
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
