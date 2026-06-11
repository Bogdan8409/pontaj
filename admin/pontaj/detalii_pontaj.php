<?php
// Conectare la baza de date
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

$id = $_GET['id'] ?? 0;

$sql = "SELECT p.*, c.nume 
        FROM pontaje p 
        JOIN clienti c ON p.client_id = c.id 
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$date = $result->fetch_assoc();

if (!$date) {
    die("Pontajul nu a fost găsit!");
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <title>Detalii Pontaj | Administrare</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
      </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="../index.php" class="brand-link">
        <span class="brand-text font-weight-light">Gestionare Pontaje</span>
      </a>
      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column">
            <li class="nav-item">
              <a href="../index.php" class="nav-link active">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="../pontaje.php" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>Pontaje</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="../rapoarte.php" class="nav-link">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Rapoarte</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="../setari.php" class="nav-link">
                <i class="nav-icon fas fa-cog"></i>
                <p>Setări</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid pt-3">
          <div class="row mb-2">
            <div class="col-sm-12">
              <h2>Detalii Pontaj #<?= $date['id'] ?></h2>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <p class="mb-1"><b>Client:</b></p>
                  <h5><?= htmlspecialchars($date['nume']) ?></h5>
                </div>
                <div class="col-md-4">
                  <p class="mb-1"><b>Serie Factură:</b></p>
                  <h5><?= htmlspecialchars($date['serie_factura']) ?></h5>
                </div>
                <div class="col-md-4">
                  <p class="mb-1"><b>Zile Facturate:</b></p>
                  <h5><?= htmlspecialchars($date['zile_facturate']) ?></h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
