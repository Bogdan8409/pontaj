<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';

$conn = new mysqli("localhost", "root", "", "2web_pontaj");

/* UPDATE STATUS */
if (isset($_POST['update_status'])) {
  $id = intval($_POST['id']);
  $status = $_POST['status'];

  $stmt = $conn->prepare("UPDATE pontaje SET status=? WHERE id=?");
  $stmt->bind_param("si", $status, $id);
  $stmt->execute();
}

/* FILTRARE */
$allowedFilters = ['pending', 'aprobat', 'respins'];
$filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowedFilters) ? $_GET['filter'] : '';

if ($filter !== '') {
  $stmt = $conn->prepare("SELECT id, luna_facturare, data_trimitere, nume_prenume, client_id, status
                          FROM pontaje WHERE status=? ORDER BY id DESC");
  $stmt->bind_param("s", $filter);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT id, luna_facturare, data_trimitere, nume_prenume, client_id, status
                          FROM pontaje ORDER BY id DESC");
}

$totalPending  = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE status='pending'")->fetch_assoc()['total'];
$totalAprobate = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE status='aprobat' AND MONTH(data_trimitere) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'];
$totalRespinse = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE status='respins' AND MONTH(data_trimitere) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'];
$totalFacturi  = $conn->query("SELECT COUNT(*) AS total FROM pontaje WHERE MONTH(luna_facturare) = MONTH(CURRENT_DATE())")->fetch_assoc()['total'];

$cardTitles = [
  ''        => 'Toate pontajele',
  'pending' => 'Pontaje în așteptare',
  'aprobat' => 'Pontaje aprobate',
  'respins' => 'Pontaje respinse',
];
$cardTitle = $cardTitles[$filter];
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="utf-8">
  <title>Dashboard | Administrare Pontaje</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
  <style>
    /* Overlay popup */
    #confirmModal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    #confirmModal.show { display: flex; }
    #confirmModal .modal-box {
      background: #fff;
      border-radius: 8px;
      padding: 28px 32px;
      width: 360px;
      box-shadow: 0 8px 32px rgba(0,0,0,.2);
      text-align: center;
    }
    #confirmModal .modal-box i {
      font-size: 2.4rem;
      color: #f39c12;
      margin-bottom: 12px;
    }
    #confirmModal .modal-box h5 {
      margin-bottom: 6px;
      font-weight: 600;
    }
    #confirmModal .modal-box p {
      color: #555;
      margin-bottom: 20px;
      font-size: .95rem;
    }
    #confirmModal .modal-actions { display: flex; gap: 10px; justify-content: center; }
  </style>
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
        <?php echo "<span class='nav-link'>Bun venit, <b>{$adminName}</b></span>"; ?>
        <li class="nav-item">
          <a class="nav-link" href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </li>
      </div>
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

      <section class="content">
        <!-- Statistici -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= $totalPending ?></h3>
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

        <!-- Tabel Pontaje -->
        <div class="card">
          <div class="card-header d-flex align-items-center flex-wrap gap-2">
            <h3 class="card-title mr-3"><?= $cardTitle ?></h3>
            <div class="btn-group ml-2">
              <a href="index.php"
                 class="btn btn-sm <?= $filter === '' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                <i class="fas fa-list"></i> Toate
              </a>
              <a href="index.php?filter=pending"
                 class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                <i class="fas fa-clock"></i> În așteptare
                <span class="badge badge-light ml-1"><?= $totalPending ?></span>
              </a>
              <a href="index.php?filter=aprobat"
                 class="btn btn-sm <?= $filter === 'aprobat' ? 'btn-success' : 'btn-outline-success' ?>">
                <i class="fas fa-check"></i> Aprobate
              </a>
              <a href="index.php?filter=respins"
                 class="btn btn-sm <?= $filter === 'respins' ? 'btn-danger' : 'btn-outline-danger' ?>">
                <i class="fas fa-times"></i> Respinse
              </a>
            </div>
            <a href="pontaje.php" class="btn btn-primary btn-sm ml-auto">
              Vezi toate pontajele
            </a>
          </div>

          <div class="card-body">
            <table class="table table-bordered table-hover">
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
                    <form method="POST" action="index.php<?= $filter ? '?filter=' . $filter : '' ?>" class="status-form">

                      <td><?= htmlspecialchars($row['data_trimitere']) ?></td>
                      <td><?= htmlspecialchars($row['nume_prenume']) ?></td>
                      <td><?= htmlspecialchars($row['client_id']) ?></td>

                      <td>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" class="form-control form-control-sm status-select"
                                data-original="<?= $row['status'] ?>">
                          <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>În așteptare</option>
                          <option value="aprobat" <?= $row['status'] === 'aprobat' ? 'selected' : '' ?>>Aprobat</option>
                          <option value="respins" <?= $row['status'] === 'respins' ? 'selected' : '' ?>>Respins</option>
                        </select>
                      </td>

                      <td>
                        <a href="pontaj/view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i> Vezi
                        </a>
                      </td>

                    </form>
                  </tr>
                <?php endwhile; ?>

                <?php if ($result->num_rows === 0): ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">Niciun pontaj găsit.</td>
                  </tr>
                <?php endif; ?>
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

  <!-- Popup confirmare -->
  <div id="confirmModal">
    <div class="modal-box">
      <i class="fas fa-question-circle"></i>
      <h5>Confirmare schimbare status</h5>
      <p id="confirmText">Ești sigur că vrei să salvezi noul status?</p>
      <div class="modal-actions">
        <button id="btnCancel" class="btn btn-secondary">
          <i class="fas fa-times"></i> Anulează
        </button>
        <button id="btnConfirm" class="btn btn-success">
          <i class="fas fa-save"></i> Salvează
        </button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

  <script>
    const statusLabels = {
      pending: 'În așteptare',
      aprobat: 'Aprobat',
      respins: 'Respins'
    };

    const modal     = document.getElementById('confirmModal');
    const btnCancel = document.getElementById('btnCancel');
    const btnConfirm= document.getElementById('btnConfirm');
    const confirmText = document.getElementById('confirmText');

    let pendingForm = null;
    let pendingSelect = null;

    // Intercept schimbare select
    document.querySelectorAll('.status-select').forEach(function(select) {
      select.addEventListener('change', function() {
        pendingSelect = this;
        pendingForm   = this.closest('form');

        const newLabel = statusLabels[this.value];
        confirmText.textContent = 'Dorești să schimbi statusul în „' + newLabel + '"?';

        modal.classList.add('show');
      });
    });

    // Anulează — resetează select la valoarea originală
    btnCancel.addEventListener('click', function() {
      if (pendingSelect) {
        pendingSelect.value = pendingSelect.dataset.original;
      }
      modal.classList.remove('show');
      pendingForm = null;
      pendingSelect = null;
    });

    // Confirmă — trimite formularul
    btnConfirm.addEventListener('click', function() {
      modal.classList.remove('show');
      if (pendingForm) pendingForm.submit();
    });

    // Închide la click pe overlay
    modal.addEventListener('click', function(e) {
      if (e.target === modal) btnCancel.click();
    });
  </script>
</body>
</html>