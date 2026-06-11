<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "2web_pontaj");

/* UPDATE STATUS */

if (isset($_POST['update_status'])) {
  $id = intval($_POST['id']);
  $status = $_POST['status'];

  $stmt = $conn->prepare("UPDATE pontaje SET status=? WHERE id=?");
  $stmt->bind_param("si", $status, $id);
  $stmt->execute();
}

/* SELECT FACTURI */

$sql = "SELECT id, luna_facturare, data_trimitere, nume_prenume, client_id, status
        FROM pontaje
        ORDER BY id DESC";


$result = $conn->query($sql);
$totalPending = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE status='pending'")->fetch_assoc()['total'];
$totalAprobate = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE status='aprobat' AND MONTH(data_trimitere) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'];
$totalRespinse = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE status='respins' AND MONTH(data_trimitere) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'];
$totalFacturi = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE MONTH(luna_facturare) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="utf-8">admin1@2web.ro
  <title>Dashboard | Administrare Pontaje</title>

  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
      </ul>
      <div class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </li>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="index.php" class="brand-link">
        <span class="brand-text font-weight-light">Gestionare Pontaje</span>
      </a>

      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column">
            <li class="nav-item">
              <a href="index.php" class="nav-link active">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="pontaje.php" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>Pontaje</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="rapoarte.php" class="nav-link">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Rapoarte</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="setari.php" class="nav-link">
                <i class="nav-icon fas fa-cog"></i>
                <p>Setări</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
      <section class="content-header">
        <h1>Dashboard</h1>
      </section>

      <!-- Statistici Overview -->

      <section class="content">
        <div class="row">


          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3> <?php echo $totalPending; ?></h3>
                <p>Pontaje în așteptare</p>
              </div>
              <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $totalAprobate ?></h3>
                <p>Pontaje aprobate (luna curentă)</p>
              </div>
              <div class="icon"><i class="fas fa-check"></i></div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?= $totalRespinse ?></h3>
                <p>Pontaje respinse (luna curentă)</p>
              </div>
              <div class="icon"><i class="fas fa-times"></i></div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $totalFacturi ?></h3>
                <p>Facturi generate (luna curentă)</p>
              </div>
              <div class="icon"><i class="fas fa-file-invoice"></i></div>
            </div>
          </div>

        </div>

        <!-- Lista Pontaje Pending -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Pontaje în așteptare</h3>
            <a href="pontaje.php" class="btn btn-primary btn-sm float-right">
              Vezi toate pontajele
            </a>
          </div>

          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Data trimitere</th>
                  <th>Colaborator</th>
                  <th>Client</th>
                  <th>Status</th>
                  <th>Acțiuni</th>
                </tr>
              </thead>
              <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                  <tr>

                    <form method="POST">

                      <td><?= $row['data_trimitere'] ?></td>

                      <td><?= htmlspecialchars($row['nume_prenume']) ?></td>

                      <td><?= htmlspecialchars($row['client_id']) ?></td>

                      <td>

                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                        <select name="status" class="form-select form-select-sm">
                          
                          <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>In așteptare</option>

                          <option value="aprobat" <?= $row['status'] == 'aprobat' ? 'selected' : '' ?>>Aprobat</option>
                          
                          <option value="respins" <?= $row['status'] == 'respins' ? 'selected' : '' ?>>Respins</option>

                        </select>

                      </td>

                      <td>

                        <button type="submit" name="update_status" class="btn btn-sm btn-success">
                          <i class="fas fa-save"></i>
                        </button>

                        <a href="pontaj/view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                          Vezi
                        </a>

                      </td>

                    </form>

                  </tr>

                <?php endwhile; ?>

              </tbody>

              </tr>
              </tbody>
            </table>
          </div>
        </div>

      </section>
    </div>

    <footer class="main-footer text-center">
      2WEB SOFTWARE SRL
    </footer>

  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>

</html>