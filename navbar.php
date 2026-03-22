<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<?php
$new_contacts = 0;
if (($_SESSION['role'] ?? '') === 'admin') {
    require_once "db.php";
    $res = $conn->query("SELECT COUNT(*) c FROM contact_messages WHERE status='new'");
    if ($res) {
        $new_contacts = (int)$res->fetch_assoc()['c'];
    }
}
?>
<div class="nav-back fixed-top"></div>
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand me-auto" href="index.php">StudySpot</a>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
      <div class="offcanvas-header" style="background-color:#186753ff;">
        <h5 class="offcanvas-title">
          <a class="navbar-brand me-auto" href="index.php">StudySpot</a>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>

      <div class="offcanvas-body" style="background-color: var(--primary);">
        <ul class="navbar-nav justify-content-center flex-grow-1 pe-3">
          <li class="nav-item"><a class="nav-link mx-lg-5" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link mx-lg-5" href="spots.php">Spots</a></li>
          <li class="nav-item"><a class="nav-link mx-lg-5" href="kontakt.php">Kontakt</a></li>


          <?php if (isset($_SESSION["user_id"])): ?>
            <?php if (in_array($_SESSION["role"] ?? "", ["owner"], true)): ?>
              <li class="nav-item"><a class="nav-link mx-lg-5" href="ort_anmelden.php">Ort anmelden</a></li>
            <?php endif; ?>

            <?php if (($_SESSION["role"] ?? "") === "owner"): ?>
              <li class="nav-item"><a class="nav-link mx-lg-5" href="owner_home.php">Meine Orte</a></li>
            <?php endif; ?>

            <?php if (($_SESSION["role"] ?? "") === "admin"): ?>
              <li class="nav-item"><a class="nav-link mx-lg-5" href="admin_requests.php">Admin</a></li>
              <li class="nav-item">
        <a class="nav-link" href="admin_contacts.php">
            📩 Kontakt Inbox
            <?php if ($new_contacts > 0): ?>
                <span class="badge bg-danger ms-1"><?= $new_contacts ?></span>
            <?php endif; ?>
        </a>
    </li>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (isset($_SESSION["user_id"])): ?>
            <li class="nav-item d-lg-none">
              <a class="nav-link mx-lg-5" href="account.php">
                Mein Account (<?= htmlspecialchars($_SESSION["user_name"]) ?>)
              </a>
            </li>
            <li class="nav-item d-lg-none">
              <a class="nav-link mx-lg-5" href="logout.php">Logout</a>
            </li>
          <?php else: ?>
            <li class="nav-item d-lg-none">
              <a class="nav-link mx-lg-5" href="login.php">Login</a>
            </li>
            <li class="nav-item d-lg-none">
              <a class="nav-link mx-lg-5" href="register.php">Registrieren</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <?php if (isset($_SESSION["user_id"])): ?>
      <a href="account.php" class="login-button d-none d-lg-inline">
        <?= htmlspecialchars($_SESSION["user_name"]) ?>
      </a>
      <a href="logout.php" class="login-button d-none d-lg-inline" style="margin-left:10px;">
        Logout
      </a>
    <?php else: ?>
      <a href="login.php" class="login-button d-none d-lg-inline">Login</a>
      <a href="register.php" class="login-button d-none d-lg-inline" style="margin-left:10px;">
        Registrieren
      </a>
    <?php endif; ?>

    <button class="navbar-toggler pe-0" type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>
