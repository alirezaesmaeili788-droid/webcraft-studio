<?php
session_start();
require "db.php";

/**
 * Owner Dashboard / Account-Home für Owner
 * - zeigt eigene Spots (approved) und eigene Ort-Anfragen (place_requests)
 * - Admin darf auch rein
 */

// Login nötig
if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$user_id = (int)$_SESSION["user_id"];

// Rolle sicherstellen (falls noch nicht in Session)
if (!isset($_SESSION["role"])) {
  $stmt = $conn->prepare("SELECT role, first_name, last_name FROM users WHERE id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $_SESSION["role"] = $u["role"] ?? "student";
  $_SESSION["first_name"] = $_SESSION["first_name"] ?? ($u["first_name"] ?? "");
  $_SESSION["last_name"]  = $_SESSION["last_name"]  ?? ($u["last_name"] ?? "");
}

$role = $_SESSION["role"] ?? "student";
if (!in_array($role, ["owner","admin"], true)) {
  http_response_code(403);
  echo "403 - Kein Zugriff (nur Owner/Admin)";
  exit;
}

// Eigene Spots laden
$spots = [];
$stmt = $conn->prepare("SELECT id, name, type, address, zip, city, image_url, wifi, power_outlets, group_friendly, quiet_level, created_at
                        FROM spots
                        WHERE created_by=?
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $spots[] = $row;
$stmt->close();

// Eigene Requests laden (wenn Tabelle/Spalten existieren)
$requests = [];
$requests_error = null;

try {
  // Falls du created_by anders nennst, hier anpassen:
  $stmt = $conn->prepare("SELECT id, place_name AS name, place_type AS type, street AS address, zip, city, hours, status, photo_url, created_at
                          FROM place_requests
                          WHERE created_by=?
                          ORDER BY created_at DESC");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) $requests[] = $row;
  $stmt->close();
} catch (Throwable $e) {
  // Wenn place_requests keine created_by Spalte hat o.ä.
  $requests_error = "Hinweis: Deine Tabelle place_requests hat vermutlich keine Spalte 'created_by' oder existiert nicht im aktuellen Schema.";
}

// kleine Helfer
function badge_status($status) {
  $status = $status ?? "pending";
  $map = [
    "pending"  => ["text"=>"Pending",  "class"=>"bg-warning text-dark"],
    "approved" => ["text"=>"Approved", "class"=>"bg-success"],
    "rejected" => ["text"=>"Rejected", "class"=>"bg-danger"],
  ];
  $b = $map[$status] ?? ["text"=>$status, "class"=>"bg-secondary"];
  return '<span class="badge '.$b["class"].'">'.$b["text"].'</span>';
}

function yesno_badge($v, $yes="Ja", $no="Nein") {
  return $v ? '<span class="badge bg-success">'.$yes.'</span>' : '<span class="badge bg-secondary">'.$no.'</span>';
}

$first = htmlspecialchars($_SESSION["first_name"] ?? "");
$last  = htmlspecialchars($_SESSION["last_name"] ?? "");
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>StudySpot | Owner Bereich</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="spots.php">StudySpot</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="spots.php">Spots</a></li>

        <?php if (in_array($role, ["owner","admin"], true)): ?>
          <li class="nav-item"><a class="nav-link" href="ort_anmelden.php">Ort anmelden</a></li>
          <li class="nav-item"><a class="nav-link active" href="owner_home.php">Owner Bereich</a></li>
        <?php endif; ?>

        <li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h1 class="h3 mb-1">Owner Bereich</h1>
      <div class="text-muted">Hallo <?= trim($first." ".$last) !== "" ? $first." ".$last : "!" ?> — Rolle: <span class="badge bg-primary"><?= htmlspecialchars($role) ?></span></div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="ort_anmelden.php">+ Ort anmelden</a>
      <a class="btn btn-outline-secondary" href="spots.php">Alle Spots</a>
    </div>
  </div>

  <ul class="nav nav-tabs" id="ownerTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="spots-tab" data-bs-toggle="tab" data-bs-target="#spots-pane" type="button" role="tab">
        Meine Spots (<?= count($spots) ?>)
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="req-tab" data-bs-toggle="tab" data-bs-target="#req-pane" type="button" role="tab">
        Meine Anfragen (<?= count($requests) ?>)
      </button>
    </li>
  </ul>

  <div class="tab-content border border-top-0 bg-white p-3 rounded-bottom shadow-sm">
    <!-- Spots -->
    <div class="tab-pane fade show active" id="spots-pane" role="tabpanel" aria-labelledby="spots-tab">
      <?php if (empty($spots)): ?>
        <div class="alert alert-info mb-0">
          Du hast noch keine freigegebenen Spots. Melde einen Ort an, dann kann der Admin ihn freigeben.
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($spots as $s): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100">
                <?php if (!empty($s["image_url"]) && file_exists(__DIR__ . "/" . $s["image_url"])): ?>
                  <img src="<?= htmlspecialchars($s["image_url"]) ?>" class="card-img-top" alt="Spot Bild">
                <?php endif; ?>

                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between gap-2">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($s["name"]) ?></h5>
                    <span class="badge bg-dark"><?= htmlspecialchars($s["type"]) ?></span>
                  </div>
                  <div class="text-muted small mb-2"><?= htmlspecialchars($s["zip"]." ".$s["city"]) ?></div>

                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <?= yesno_badge((int)$s["wifi"], "WLAN", "kein WLAN") ?>
                    <?= yesno_badge((int)$s["power_outlets"], "Steckdosen", "keine Steckdosen") ?>
                    <?= yesno_badge((int)$s["group_friendly"], "Gruppe ok", "kein Gruppe") ?>
                    <?php if (!empty($s["quiet_level"])): ?>
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($s["quiet_level"]) ?></span>
                    <?php endif; ?>
                  </div>

                  <div class="small text-muted">
                    Erstellt: <?= htmlspecialchars($s["created_at"] ?? "") ?>
                  </div>
                </div>

                <div class="card-footer bg-white d-flex gap-2">
                  <a class="btn btn-sm btn-outline-primary" href="spot.php?id=<?= (int)$s["id"] ?>">Ansehen</a>
                  <a class="btn btn-sm btn-outline-warning" href="spot_edit.php?id=<?= (int)$s["id"] ?>">Bearbeiten</a>
                  <a class="btn btn-sm btn-outline-danger" href="spot_delete.php?id=<?= (int)$s["id"] ?>">Löschen</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Requests -->
    <div class="tab-pane fade" id="req-pane" role="tabpanel" aria-labelledby="req-tab">
      <?php if ($requests_error): ?>
        <div class="alert alert-warning">
          <?= htmlspecialchars($requests_error) ?>
          <div class="small mt-2">
            Wenn du möchtest, sag mir kurz, ob place_requests die Spalte <code>created_by</code> hat – dann passe ich die Query passend an.
          </div>
        </div>
      <?php endif; ?>

      <?php if (empty($requests)): ?>
        <div class="alert alert-info mb-0">
          Du hast noch keine Anfragen. Nutze „Ort anmelden“, um einen Ort einzureichen.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Ort</th>
                <th>Typ</th>
                <th>Adresse</th>
                <th>Status</th>
                <th>Erstellt</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $r): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($r["name"]) ?></td>
                  <td><?= htmlspecialchars($r["type"]) ?></td>
                  <td><?= htmlspecialchars($r["zip"]." ".$r["city"].", ".$r["address"]) ?></td>
                  <td><?= badge_status($r["status"] ?? "pending") ?></td>
                  <td class="text-muted small"><?= htmlspecialchars($r["created_at"] ?? "") ?></td>
                </tr>
                <?php if (!empty($r["hours"]) || !empty($r["photo_url"])): ?>
                  <tr>
                    <td colspan="5" class="bg-light">
                      <?php if (!empty($r["hours"])): ?>
                        <div><strong>Öffnungszeiten:</strong> <?= nl2br(htmlspecialchars($r["hours"])) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($r["photo_url"]) && file_exists(__DIR__ . "/" . $r["photo_url"])): ?>
                        <div class="mt-2">
                          <img src="<?= htmlspecialchars($r["photo_url"]) ?>" alt="Request Foto" style="max-width:260px;border-radius:12px;">
                        </div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
