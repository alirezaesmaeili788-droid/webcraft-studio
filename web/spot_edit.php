<?php
require_once "auth.php"; // erzwingt Login
require "db.php";

// Nur Owner oder Admin
require_role(['admin','owner']);
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: spots.php"); exit; }

// Spot laden
$stmt = $conn->prepare("SELECT * FROM spots WHERE id=?");
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

$errors = [];
$success = "";

// Hilfsfunktion für Upload
function handle_spot_upload(array $file, string $currentPath = ""): array {
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return [true, $currentPath, ""]; // nichts geändert
  }

  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    return [false, $currentPath, "Upload-Fehler."];
  }

  $maxBytes = 2 * 1024 * 1024; // 2MB
  if (($file['size'] ?? 0) > $maxBytes) {
    return [false, $currentPath, "Bild ist zu groß (max. 2MB)."];
  }

  $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
  $allowed = ['jpg','jpeg','png','webp'];
  if (!in_array($ext, $allowed, true)) {
    return [false, $currentPath, "Nur JPG, PNG oder WEBP erlaubt."];
  }

  $targetDir = __DIR__ . "/uploads/spots";
  if (!is_dir($targetDir)) {
    @mkdir($targetDir, 0775, true);
  }

  $safeName = "spot_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
  $targetFs = $targetDir . "/" . $safeName;
  $targetWeb = "uploads/spots/" . $safeName;

  if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
    return [false, $currentPath, "Konnte Bild nicht speichern."];
  }

  return [true, $targetWeb, ""];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $type = trim($_POST['type'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $zip = trim($_POST['zip'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $opening_hours = trim($_POST['opening_hours'] ?? '');
  $description = trim($_POST['description'] ?? '');

  $wifi = isset($_POST['wifi']) ? 1 : 0;
  $power_outlets = isset($_POST['power_outlets']) ? 1 : 0;
  $group_friendly = isset($_POST['group_friendly']) ? 1 : 0;
  $quiet_level = trim($_POST['quiet_level'] ?? 'medium');

  $allowedTypes = ["Cafe","Bibliothek","Uni","CoWorking","Sonstiges"];
  $allowedQuiet = ['quiet','medium','loud'];

  if ($name === '' || $address === '' || $zip === '' || $city === '') {
    $errors[] = "Bitte Name, Adresse, PLZ und Stadt ausfüllen.";
  }
  if (!in_array($type, $allowedTypes, true)) {
    $errors[] = "Bitte einen gültigen Typ wählen.";
  }
  if (!in_array($quiet_level, $allowedQuiet, true)) {
    $quiet_level = 'medium';
  }

  // Upload (optional)
  $newImagePath = $spot['image_url'] ?? '';
  if (!$errors) {
    [$ok, $newImagePath, $msg] = handle_spot_upload($_FILES['image'] ?? [], $newImagePath);
    if (!$ok) $errors[] = $msg;
  }

  if (!$errors) {
    $stmt = $conn->prepare(
      "UPDATE spots
       SET name=?, type=?, address=?, zip=?, city=?, opening_hours=?, description=?, image_url=?,
           wifi=?, power_outlets=?, quiet_level=?, group_friendly=?
       WHERE id=?"
    );
    $stmt->bind_param(
      "ssssssssissii",
      $name, $type, $address, $zip, $city, $opening_hours, $description, $newImagePath,
      $wifi, $power_outlets, $quiet_level, $group_friendly,
      $id
    );
    $stmt->execute();
    $stmt->close();

    // Neu laden
    $stmt = $conn->prepare("SELECT * FROM spots WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $spot = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $success = "Spot wurde gespeichert.";
  }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudySpot – Spot bearbeiten</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>
<body>

<?php include "navbar.php"; ?>

<main class="page-space">
  <div class="wrap" style="max-width: 900px;">

    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
      <h1 style="margin:0; color:#1F5E3B;">Spot bearbeiten</h1>
    </div>

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
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="bg-white p-4" style="border-radius:20px; border:1px solid var(--border); box-shadow:var(--shadow);">

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Name</label>
          <input class="form-control" name="name" value="<?= htmlspecialchars($spot['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Typ</label>
          <select class="form-select" name="type" required>
            <?php foreach (["Cafe","Bibliothek","Uni","CoWorking","Sonstiges"] as $t): ?>
              <option value="<?= $t ?>" <?= (($spot['type'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-8">
          <label class="form-label">Adresse</label>
          <input class="form-control" name="address" value="<?= htmlspecialchars($spot['address'] ?? '') ?>" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">PLZ</label>
          <input class="form-control" name="zip" value="<?= htmlspecialchars($spot['zip'] ?? '') ?>" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Stadt</label>
          <input class="form-control" name="city" value="<?= htmlspecialchars($spot['city'] ?? '') ?>" required>
        </div>

        <div class="col-md-12">
          <label class="form-label">Öffnungszeiten</label>
          <input class="form-control" name="opening_hours" value="<?= htmlspecialchars($spot['opening_hours'] ?? '') ?>" placeholder="z.B. Mo–Fr 08:00–18:00">
        </div>

        <div class="col-md-12">
          <label class="form-label">Beschreibung</label>
          <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($spot['description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label">Lautstärke</label>
          <select class="form-select" name="quiet_level">
            <option value="quiet" <?= (($spot['quiet_level'] ?? '') === 'quiet') ? 'selected' : '' ?>>ruhig</option>
            <option value="medium" <?= (($spot['quiet_level'] ?? '') === 'medium') ? 'selected' : '' ?>>mittel</option>
            <option value="loud" <?= (($spot['quiet_level'] ?? '') === 'loud') ? 'selected' : '' ?>>laut</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Bild (optional)</label>
          <input class="form-control" type="file" name="image" accept="image/png,image/jpeg,image/webp">
          <div class="form-text">Max. 2MB, JPG/PNG/WEBP.</div>
        </div>

        <div class="col-md-12">
          <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="wifi" name="wifi" <?= !empty($spot['wifi']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="wifi">WLAN</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="power_outlets" name="power_outlets" <?= !empty($spot['power_outlets']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="power_outlets">Steckdosen</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="group_friendly" name="group_friendly" <?= !empty($spot['group_friendly']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="group_friendly">Gruppenfreundlich</label>
            </div>
          </div>
        </div>

        <div class="col-md-12 d-flex gap-2">
          <button class="btn btn-primary" type="submit" style="border-radius:999px;">Speichern</button>
          <a class="btn btn-outline" href="spot.php?id=<?= (int)$spot['id'] ?>" style="border-radius:999px;">Abbrechen</a>
        </div>
              <a href="spot.php?id=<?= (int)$spot['id'] ?>" class="btn btn-outline" style="border-radius:999px;">← Zurück</a>

      </div>

    </form>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
