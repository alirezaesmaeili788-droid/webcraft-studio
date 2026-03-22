<?php
require "auth.php";
require "db.php";

if (($_SESSION["user_id"] ?? 0) != 1) { header("Location: index.php"); exit; }

// Approve
if (isset($_GET["approve"])) {
  $rid = (int)$_GET["approve"];

  $stmt = $conn->prepare("SELECT * FROM place_requests WHERE id=? AND status='pending'");
  $stmt->bind_param("i", $rid);
  $stmt->execute();
  $req = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($req) {
    $map = [
      "cafe" => "Cafe",
      "bibliothek" => "Bibliothek",
      "coworking" => "CoWorking",
      "sonstiges" => "Sonstiges"
    ];
    $type = $map[$req["place_type"]] ?? "Sonstiges";

    $name = $req["place_name"];
    $address = $req["street"];
    $zip = $req["zip"];
    $city = $req["city"];
    $desc = $req["description"];
    $img  = $req["photo_url"]; 
    $hours = $req["hours"];

    $features = json_decode($req["features"] ?? "[]", true);
    if (!is_array($features)) $features = [];
    $wifi = in_array("wifi", $features, true) ? 1 : 0;
    $power = in_array("steckdosen", $features, true) ? 1 : 0;

    $suitable = json_decode($req["suitable"] ?? "[]", true);
    if (!is_array($suitable)) $suitable = [];
    $group = in_array("gruppenarbeit", $suitable, true) ? 1 : 0;

    $quiet = "Mittel"; 

    $stmt = $conn->prepare("
      INSERT INTO spots (name,type,address,zip,city,opening_hours,description,image_url,wifi,power_outlets,quiet_level,group_friendly,created_by)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $createdBy = (int)($req["created_by"] ?? ($_SESSION["user_id"] ?? 0));
    $stmt->bind_param(
      "ssssssssssssi",
      $name,$type,$address,$zip,$city,$hours,$desc,$img,$wifi,$power,$quiet,$group,$createdBy
    );
    $stmt->execute();
    $newSpotId = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("UPDATE place_requests SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $stmt->close();

    header("Location: spot.php?id=".$newSpotId);
    exit;
  }
}

$res = $conn->query("SELECT id, place_name, email, city, created_at FROM place_requests WHERE status='pending' ORDER BY created_at DESC");
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Ort-Anfragen</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "navbar.php"; ?>

<div class="container py-5" style="max-width: 900px;">
  <h1 class="h3 fw-bold mb-3">Ort-Anfragen (pending)</h1>

  <?php if (!$rows): ?>
    <div class="alert alert-info">Keine offenen Anfragen.</div>
  <?php endif; ?>

  <div class="list-group">
    <?php foreach ($rows as $r): ?>
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold"><?= htmlspecialchars($r["place_name"]) ?></div>
          <div class="small text-muted"><?= htmlspecialchars($r["city"]) ?> • <?= htmlspecialchars($r["email"]) ?> • <?= htmlspecialchars($r["created_at"]) ?></div>
        </div>
        <a class="btn btn-success btn-sm" href="admin_requests.php?approve=<?= (int)$r["id"] ?>">Approve → Spot</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
