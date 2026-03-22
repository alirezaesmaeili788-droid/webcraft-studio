<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once "auth.php";
require_role(['owner','admin']); // student darf NICHT rein
require "db.php";

$errors = [];
$success = false;

function old($key, $default="") {
  return htmlspecialchars($_POST[$key] ?? $default);
}

// Submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $place_type = trim($_POST["place_type"] ?? "");
  $place_name = trim($_POST["place_name"] ?? "");
  $contact_person = trim($_POST["contact_person"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $phone = trim($_POST["phone"] ?? "");
  $street = trim($_POST["street"] ?? "");
  $zip = trim($_POST["zip"] ?? "");
  $city = trim($_POST["city"] ?? "");
  $district = $_POST["district"] ?? "";
  $website = trim($_POST["website"] ?? "");
  $hours = trim($_POST["hours"] ?? "");
  $description = trim($_POST["description"] ?? "");
  $notes = trim($_POST["notes"] ?? "");
  $consent = isset($_POST["consent"]);

  $suitable = $_POST["suitable"] ?? [];
  $features = $_POST["features"] ?? [];

  // Pflichtfelder
  if ($place_type==="" || $place_name==="" || $email==="" || $street==="" || $zip==="" || $city==="" || $hours==="" || $description==="") {
    $errors[] = "Bitte alle Pflichtfelder (*) ausfüllen.";
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Bitte eine gültige E-Mail eingeben.";
  }
  if (!$consent) {
    $errors[] = "Bitte die Einwilligung bestätigen.";
  }

  // Bezirk validieren (optional)
  $districtVal = null;
  if ($district !== "") {
    $d = (int)$district;
    if ($d < 1 || $d > 23) $errors[] = "Bezirk muss zwischen 1 und 23 sein.";
    else $districtVal = $d;
  }

  // Foto Upload (optional)
  $photoUrl = null;
  if (isset($_FILES["photo"]) && !empty($_FILES["photo"]["name"])) {

    $allowedExt = ["jpg","jpeg","png","webp"];
    $maxSize = 3 * 1024 * 1024; // 3MB

    $fileName = $_FILES["photo"]["name"];
    $fileTmp  = $_FILES["photo"]["tmp_name"];
    $fileSize = (int)($_FILES["photo"]["size"] ?? 0);
    $fileErr  = (int)($_FILES["photo"]["error"] ?? 0);

    if ($fileErr !== UPLOAD_ERR_OK) {
      $errors[] = "Foto konnte nicht hochgeladen werden (Upload-Fehler).";
    } else {
      $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowedExt, true)) {
        $errors[] = "Foto: nur JPG, PNG oder WEBP erlaubt.";
      }
      if ($fileSize <= 0 || $fileSize > $maxSize) {
        $errors[] = "Foto: maximal 3 MB erlaubt.";
      }

      // MIME Check (sicherer)
      if (!$errors) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileTmp);
        $allowedMime = ["image/jpeg","image/png","image/webp"];
        if (!in_array($mime, $allowedMime, true)) {
          $errors[] = "Foto: ungültiger Bildtyp.";
        }
      }

      // Speichern
      if (!$errors) {
        $uploadDirFs = __DIR__ . "/uploads/requests/";
        if (!is_dir($uploadDirFs)) {
          mkdir($uploadDirFs, 0777, true);
        }

        $newName = uniqid("req_", true) . "." . $ext;
        $targetFs = $uploadDirFs . $newName;
        $targetWeb = "uploads/requests/" . $newName;

        if (!move_uploaded_file($fileTmp, $targetFs)) {
          $errors[] = "Foto konnte nicht gespeichert werden.";
        } else {
          $photoUrl = $targetWeb;
        }
      }
    }
  }

  // Speichern in DB
  if (!$errors) {
    $suitableJson = json_encode($suitable, JSON_UNESCAPED_UNICODE);
    $featuresJson = json_encode($features, JSON_UNESCAPED_UNICODE);
    $created_by = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("
      INSERT INTO place_requests
      (place_type, place_name, contact_person, email, phone, street, zip, city, district, website, hours, suitable, features, description, notes, photo_url, created_by)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
      "ssssssssisssssssi",
      $place_type,
      $place_name,
      $contact_person,
      $email,
      $phone,
      $street,
      $zip,
      $city,
      $districtVal,
      $website,
      $hours,
      $suitableJson,
      $featuresJson,
      $description,
      $notes,
      $photoUrl,
      $created_by
    );

    $stmt->execute();
    $stmt->close();

    $success = true;
    $_POST = []; // Formular leeren
  }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>StudySpot | Ort anmelden</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body class="spots-body">

<?php include "navbar.php"; ?>

<div class="container py-5" style="max-width: 900px;">
  <h1 class="h3 fw-bold mb-3 text-center">Lernort / Café bei StudySpot anmelden</h1>
  <p class="small text-muted text-center mb-4">
    Fülle dieses Formular aus, wenn dein Café, deine Bibliothek oder ein anderer Lernort
    auf StudySpot erscheinen soll. Wir prüfen alle Angaben und melden uns bei dir.
  </p>

  <?php if ($success): ?>
    <div class="alert alert-success">
      Danke! Deine Anfrage wurde gesendet. Wir melden uns bald.
    </div>
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

  <!-- Formular -->
  <form method="post" enctype="multipart/form-data">

    <div class="mb-3">
      <label class="form-label">Art des Ortes *</label>
      <select name="place_type" class="form-select" required>
        <option value="">Bitte auswählen…</option>
        <option value="cafe" <?= (($_POST["place_type"] ?? "")==="cafe") ? "selected" : "" ?>>Café</option>
        <option value="bibliothek" <?= (($_POST["place_type"] ?? "")==="bibliothek") ? "selected" : "" ?>>Bibliothek</option>
        <option value="coworking" <?= (($_POST["place_type"] ?? "")==="coworking") ? "selected" : "" ?>>Coworking / Lernraum</option>
        <option value="sonstiges" <?= (($_POST["place_type"] ?? "")==="sonstiges") ? "selected" : "" ?>>Sonstiges</option>
      </select>
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Name des Ortes *</label>
        <input type="text" name="place_name" class="form-control" required value="<?= old("place_name") ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Ansprechperson (optional)</label>
        <input type="text" name="contact_person" class="form-control" value="<?= old("contact_person") ?>">
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <label class="form-label">E-Mail-Adresse *</label>
        <input type="email" name="email" class="form-control" required value="<?= old("email") ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Telefonnummer (optional)</label>
        <input type="text" name="phone" class="form-control" value="<?= old("phone") ?>">
      </div>
    </div>

    <div class="mt-3 mb-1">
      <label class="form-label">Adresse *</label>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <input type="text" name="street" class="form-control" placeholder="Straße und Hausnummer" required value="<?= old("street") ?>">
      </div>
      <div class="col-md-3">
        <input type="text" name="zip" class="form-control" placeholder="PLZ" required value="<?= old("zip") ?>">
      </div>
      <div class="col-md-3">
        <input type="text" name="city" class="form-control" placeholder="Ort" required value="<?= old("city") ?>">
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-md-4">
        <input type="number" name="district" min="1" max="23" class="form-control" placeholder="Bezirk (z.B. 6)" value="<?= old("district") ?>">
      </div>
      <div class="col-md-8">
        <input type="text" name="website" class="form-control" placeholder="Website / Instagram (optional)" value="<?= old("website") ?>">
      </div>
    </div>

    <div class="mt-3">
      <label class="form-label">Öffnungszeiten *</label>
      <textarea name="hours" class="form-control" rows="3" required><?= old("hours") ?></textarea>
    </div>

    <div class="mt-3">
      <label class="form-label">Foto hochladen (optional)</label>
      <input type="file" name="photo" class="form-control" accept="image/*">
      <div class="form-text">JPG/PNG/WEBP, max. 3MB</div>
    </div>

    <!-- geeignet -->
    <div class="mt-3">
      <label class="form-label">Wofür ist der Ort gut geeignet?</label>
      <!-- (deine checkboxen kannst du unverändert hier lassen) -->
      <!-- ... -->
    </div>

    <!-- ausstattung -->
    <div class="mt-3">
      <label class="form-label">Ausstattung</label>
      <!-- (deine checkboxen kannst du unverändert hier lassen) -->
      <!-- ... -->
    </div>

    <div class="mt-3">
      <label class="form-label">Kurzbeschreibung des Ortes *</label>
      <textarea name="description" class="form-control" rows="4" required><?= old("description") ?></textarea>
    </div>

    <div class="mt-3">
      <label class="form-label">Zusätzliche Hinweise (optional)</label>
      <textarea name="notes" class="form-control" rows="3"><?= old("notes") ?></textarea>
    </div>

    <div class="mt-3 mb-4">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="consent" id="consent" required <?= isset($_POST["consent"]) ? "checked" : "" ?>>
        <label class="form-check-label small" for="consent">
          Ich bestätige, dass ich berechtigt bin, diesen Ort anzumelden, und bin einverstanden,
          dass StudySpot meine Angaben zur Prüfung und Kontaktaufnahme speichert.
        </label>
      </div>
    </div>

    <button type="submit" class="btn btn-success w-100">Anfrage absenden</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
