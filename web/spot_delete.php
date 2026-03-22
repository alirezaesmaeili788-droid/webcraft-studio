<?php
require_once "auth.php"; // erzwingt Login
require "db.php";

// Nur Owner oder Admin
require_role(['admin', 'owner']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: spots.php'); exit; }

// Spot laden (nur was wir brauchen)
$stmt = $conn->prepare("SELECT id, name, created_by FROM spots WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$spot = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$spot) { http_response_code(404); exit('404 - Spot nicht gefunden'); }
 
// Owner darf nur eigene Spots
if (!can_manage_spot($spot['created_by'] ?? 0)) {
  http_response_code(403);
  exit('403 - Kein Zugriff');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sicherheitsfeld
  $confirm = $_POST['confirm'] ?? '';
  if ($confirm === 'yes') {
    $stmt = $conn->prepare("DELETE FROM spots WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header('Location: spots.php');
    exit;
  }

  header('Location: spot.php?id=' . $id);
  exit;
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – Spot löschen</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>
<body>

<?php include "navbar.php"; ?>

<main class="page-space">
  <div class="wrap" style="max-width: 800px;">

    <a href="spot.php?id=<?= (int)$spot['id'] ?>" class="btn btn-outline" style="border-radius:999px;">← Zurück</a>

    <div class="bg-white p-4 mt-3" style="border-radius:20px; border:1px solid var(--border); box-shadow:var(--shadow);">
      <h1 class="h4" style="color:#1F5E3B;">Spot löschen</h1>
      <p class="mb-3" style="color:var(--muted);">
        Willst du den Spot <strong><?= htmlspecialchars($spot['name']) ?></strong> wirklich löschen?
      </p>

      <form method="post" class="d-flex flex-wrap gap-2">
        <button class="btn btn-danger" name="confirm" value="yes" type="submit" style="border-radius:999px;">Ja, löschen</button>
        <button class="btn btn-outline" name="confirm" value="no" type="submit" style="border-radius:999px;">Abbrechen</button>
      </form>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
