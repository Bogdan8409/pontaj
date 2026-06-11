<?php
// Conexiune la baza de date
$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) {
    die("Eroare conectare DB: " . $mysqli->connect_error);
}

// ============================================================
// FILTRE RAPORT GENERAL (GET)
// ============================================================
$f_data_start  = $_GET['data_start']  ?? '';
$f_data_end    = $_GET['data_end']    ?? '';
$f_status      = $_GET['status']      ?? '';
$f_client      = $_GET['client']      ?? '';
$f_colaborator = $_GET['colaborator'] ?? '';
$f_luna        = $_GET['luna']        ?? '';   // format YYYY-MM

// Construieste clauza WHERE dinamica – raport general
$where   = ["1=1"];
$params  = [];
$types   = "";

if ($f_data_start !== '') {
    $where[]  = "DATE(p.data_trimitere) >= ?";
    $params[] = $f_data_start;
    $types   .= "s";
}
if ($f_data_end !== '') {
    $where[]  = "DATE(p.data_trimitere) <= ?";
    $params[] = $f_data_end;
    $types   .= "s";
}
if ($f_status !== '') {
    $where[]  = "p.status = ?";
    $params[] = $f_status;
    $types   .= "s";
}
if ($f_client !== '') {
    $where[]  = "c.nume LIKE ?";
    $params[] = "%" . $f_client . "%";
    $types   .= "s";
}
if ($f_colaborator !== '') {
    $where[]  = "p.nume_prenume LIKE ?";
    $params[] = "%" . $f_colaborator . "%";
    $types   .= "s";
}
if ($f_luna !== '') {
    // luna_facturare e de tip DATE – comparam YYYY-MM
    $where[]  = "DATE_FORMAT(p.luna_facturare, '%Y-%m') = ?";
    $params[] = $f_luna;
    $types   .= "s";
}

$where_sql = implode(" AND ", $where);

$sql_general = "SELECT 
                    p.id,
                    p.data_trimitere,
                    p.luna_facturare,
                    p.nume_prenume,
                    p.zile_facturate,
                    p.serie_factura,
                    p.numar_factura,
                    p.status,
                    p.motiv_respingere,
                    p.factura_pdf_path,
                    c.nume AS nume_client
                FROM pontaje p
                JOIN clienti c ON p.client_id = c.id
                WHERE $where_sql
                ORDER BY p.data_trimitere DESC";

if (!empty($params)) {
    $stmt = $mysqli->prepare($sql_general);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($sql_general);
}

// ============================================================
// FILTRE RAPORT LUNAR CLIENT (GET)
// ============================================================
$rc_luna   = $_GET['rc_luna']   ?? '';   // YYYY-MM
$rc_client = $_GET['rc_client'] ?? '';   // ID client

$rc_rows          = [];
$rc_total_zile    = 0;
$rc_nr_colaboratori = 0;

if ($rc_luna !== '' && $rc_client !== '') {
    $stmt_rc = $mysqli->prepare(
        "SELECT p.id, p.nume_prenume, p.luna_facturare, p.zile_facturate,
                p.serie_factura, p.numar_factura, p.status, p.factura_pdf_path,
                c.nume AS nume_client
         FROM pontaje p
         JOIN clienti c ON p.client_id = c.id
         WHERE DATE_FORMAT(p.luna_facturare, '%Y-%m') = ?
           AND p.client_id = ?
         ORDER BY p.nume_prenume ASC"
    );
    $stmt_rc->bind_param("si", $rc_luna, $rc_client);
    $stmt_rc->execute();
    $rc_result = $stmt_rc->get_result();
    while ($r = $rc_result->fetch_assoc()) {
        $rc_rows[] = $r;
        $rc_total_zile += (float)$r['zile_facturate'];
    }
    $rc_nr_colaboratori = count($rc_rows);
}

// ============================================================
// FILTRE RAPORT LUNAR COLABORATOR (GET)
// ============================================================
$rl_luna   = $_GET['rl_luna']   ?? '';   // YYYY-MM
$rl_colab  = $_GET['rl_colab']  ?? '';   // nume_prenume

$rl_rows       = [];
$rl_total_zile = 0;

if ($rl_luna !== '' && $rl_colab !== '') {
    $stmt_rl = $mysqli->prepare(
        "SELECT p.id, p.luna_facturare, p.zile_facturate,
                p.serie_factura, p.numar_factura, p.status, p.factura_pdf_path,
                c.nume AS nume_client
         FROM pontaje p
         JOIN clienti c ON p.client_id = c.id
         WHERE DATE_FORMAT(p.luna_facturare, '%Y-%m') = ?
           AND p.nume_prenume = ?
         ORDER BY c.nume ASC"
    );
    $stmt_rl->bind_param("ss", $rl_luna, $rl_colab);
    $stmt_rl->execute();
    $rl_result = $stmt_rl->get_result();
    while ($r = $rl_result->fetch_assoc()) {
        $rl_rows[] = $r;
        $rl_total_zile += (float)$r['zile_facturate'];
    }
}

// ============================================================
// LISTE pentru select-uri
// ============================================================
$clienti_result     = $mysqli->query("SELECT id, nume FROM clienti ORDER BY nume ASC");
$colaboratori_result = $mysqli->query("SELECT DISTINCT nume_prenume FROM pontaje ORDER BY nume_prenume ASC");
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="utf-8">
  <title>Rapoarte | Administrare Pontaje</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
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
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <span class="brand-text font-weight-light">Gestionare Pontaje</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="index.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="pontaje.php" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Pontaje</p></a></li>
          <li class="nav-item"><a href="rapoarte.php" class="nav-link active"><i class="nav-icon fas fa-chart-bar"></i><p>Rapoarte</p></a></li>
          <li class="nav-item"><a href="setari.php" class="nav-link"><i class="nav-icon fas fa-cog"></i><p>Setări</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper p-4">
    <h2 class="mb-4">Rapoarte</h2>

    <!-- ============================================================ -->
    <!-- RAPORT GENERAL                                               -->
    <!-- ============================================================ -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Raport General – Toate Pontajele</h3>
      </div>
      <div class="card-body">

        <!-- FILTRE GENERAL -->
        <form method="GET" action="rapoarte.php" id="form_general">
          <!-- Pastram parametrii celorlalte sectiuni ca sa nu se piarda -->
          <input type="hidden" name="rc_luna"   value="<?= htmlspecialchars($rc_luna) ?>">
          <input type="hidden" name="rc_client" value="<?= htmlspecialchars($rc_client) ?>">
          <input type="hidden" name="rl_luna"   value="<?= htmlspecialchars($rl_luna) ?>">
          <input type="hidden" name="rl_colab"  value="<?= htmlspecialchars($rl_colab) ?>">

          <div class="row g-2 mb-3">
            <div class="col-md-2">
              <label class="small text-muted mb-1">Data start</label>
              <input type="date" name="data_start" class="form-control form-control-sm"
                     value="<?= htmlspecialchars($f_data_start) ?>">
            </div>
            <div class="col-md-2">
              <label class="small text-muted mb-1">Data sfârșit</label>
              <input type="date" name="data_end" class="form-control form-control-sm"
                     value="<?= htmlspecialchars($f_data_end) ?>">
            </div>
            <div class="col-md-2">
              <label class="small text-muted mb-1">Status</label>
              <select name="status" class="form-control form-control-sm">
                <option value="">Toate</option>
                <?php foreach (['pending', 'aprobat', 'respins'] as $s): ?>
                  <option value="<?= $s ?>" <?= $f_status === $s ? 'selected' : '' ?>>
                    <?= ucfirst($s) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="small text-muted mb-1">Client</label>
              <input type="text" name="client" class="form-control form-control-sm"
                     placeholder="Caută client..."
                     value="<?= htmlspecialchars($f_client) ?>">
            </div>
            <div class="col-md-2">
              <label class="small text-muted mb-1">Colaborator</label>
              <input type="text" name="colaborator" class="form-control form-control-sm"
                     placeholder="Caută colaborator..."
                     value="<?= htmlspecialchars($f_colaborator) ?>">
            </div>
            <div class="col-md-2">
              <label class="small text-muted mb-1">Luna facturare</label>
              <input type="month" name="luna" class="form-control form-control-sm"
                     value="<?= htmlspecialchars($f_luna) ?>">
            </div>
          </div>
          <div class="d-flex gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fas fa-search mr-1"></i> Filtrează
            </button>
            <a href="rapoarte.php" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-times mr-1"></i> Resetează
            </a>
          </div>
        </form>

        <!-- BULK -->
        <button class="btn btn-success btn-sm mb-2">
          <i class="fas fa-check-double mr-1"></i> Aprobare multiplă
        </button>

        <!-- Numar rezultate -->
        <p class="text-muted small mb-2">
          <?= $result->num_rows ?> rezultate găsite
        </p>

        <!-- TABEL GENERAL -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-sm" id="tabelGeneral">
            <thead class="thead-dark">
              <tr>
                <th>ID</th>
                <th>Data trimitere</th>
                <th>Colaborator</th>
                <th>Luna facturare</th>
                <th>Client</th>
                <th>Zile facturate</th>
                <th>Factură</th>
                <th>Status</th>
                <th>Motiv respingere</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td>#<?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['data_trimitere']) ?></td>
                  <td><?= htmlspecialchars($row['nume_prenume']) ?></td>
                  <td><?= htmlspecialchars($row['luna_facturare']) ?></td>
                  <td><?= htmlspecialchars($row['nume_client']) ?></td>
                  <td><?= $row['zile_facturate'] ?></td>
                  <td>
                    <?= htmlspecialchars($row['serie_factura']) ?>
                    <?= htmlspecialchars($row['numar_factura']) ?>
                  </td>
                  <td>
                    <?php
                      $badge = match(strtolower($row['status'] ?? '')) {
                          'aprobat'  => 'badge-success',
                          'respins'  => 'badge-danger',
                          default    => 'badge-warning',
                      };
                    ?>
                    <span class="badge <?= $badge ?>">
                      <?= htmlspecialchars($row['status']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($row['motiv_respingere'] ?? '-') ?></td>
                  <td>
                    <?php if (strcasecmp($row['status'], 'aprobat') === 0 && empty($row['factura_pdf_path'])): ?>
                      <form method="post" action="actions/genereaza_factura.php">
                        <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="redirect" value="rapoarte.php">
                        <button type="submit" class="btn btn-success btn-sm">
                          <i class="fas fa-file-invoice mr-1"></i> Generează Factură
                        </button>
                      </form>
                     
                    <?php elseif (!empty($row['factura_pdf_path'])): ?>
                      <a href="../facturi/<?= htmlspecialchars(rawurlencode(basename($row['factura_pdf_path']))) ?>"
                         target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye mr-1"></i> Vezi Factură
                      </a>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- RAPORT LUNAR CLIENT                                          -->
    <!-- ============================================================ -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Raport lunar pe client</h3>
      </div>
      <div class="card-body">

        <form method="GET" action="rapoarte.php">
          <!-- Pastram parametrii raportului general -->
          <input type="hidden" name="data_start"  value="<?= htmlspecialchars($f_data_start) ?>">
          <input type="hidden" name="data_end"    value="<?= htmlspecialchars($f_data_end) ?>">
          <input type="hidden" name="status"      value="<?= htmlspecialchars($f_status) ?>">
          <input type="hidden" name="client"      value="<?= htmlspecialchars($f_client) ?>">
          <input type="hidden" name="colaborator" value="<?= htmlspecialchars($f_colaborator) ?>">
          <input type="hidden" name="luna"        value="<?= htmlspecialchars($f_luna) ?>">
          <!-- Pastram parametrii raportului colaborator -->
          <input type="hidden" name="rl_luna"  value="<?= htmlspecialchars($rl_luna) ?>">
          <input type="hidden" name="rl_colab" value="<?= htmlspecialchars($rl_colab) ?>">

          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label>Luna</label>
              <input type="month" name="rc_luna" class="form-control"
                     value="<?= htmlspecialchars($rc_luna) ?>" required>
            </div>
            <div class="col-md-3">
              <label>Client</label>
              <select name="rc_client" class="form-control" id="client_select">
                <option value="">— Selectați —</option>
                <?php
                  // Re-query deoarece result pointer poate fi epuizat
                  $cl2 = $mysqli->query("SELECT id, nume FROM clienti ORDER BY nume ASC");
                  while ($c = $cl2->fetch_assoc()):
                ?>
                  <option value="<?= $c['id'] ?>"
                    <?= (string)$rc_client === (string)$c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nume']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6 d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search mr-1"></i> Generează
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm"
                      onclick="exportTable('tabelClient','csv')">CSV</button>
              <button type="button" class="btn btn-outline-secondary btn-sm"
                      onclick="exportTable('tabelClient','excel')">Excel</button>
            </div>
          </div>
        </form>

        <?php if ($rc_luna !== '' && $rc_client !== ''): ?>
          <hr>
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="small-box bg-info">
                <div class="inner">
                  <h4><?= number_format($rc_total_zile, 2) ?></h4>
                  <p>Total zile facturate</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="small-box bg-success">
                <div class="inner">
                  <h4><?= $rc_nr_colaboratori ?></h4>
                  <p>Colaboratori</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
              </div>
            </div>
          </div>

          <?php if (!empty($rc_rows)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-sm" id="tabelClient">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Colaborator</th>
                    <th>Luna facturare</th>
                    <th>Zile facturate</th>
                    <th>Factură</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rc_rows as $r): ?>
                    <tr>
                      <td>#<?= $r['id'] ?></td>
                      <td><?= htmlspecialchars($r['nume_prenume']) ?></td>
                      <td><?= htmlspecialchars($r['luna_facturare']) ?></td>
                      <td><?= $r['zile_facturate'] ?></td>
                      <td><?= htmlspecialchars($r['serie_factura']) ?> <?= htmlspecialchars($r['numar_factura']) ?></td>
                      <td>
                        <?php
                          $badge = match(strtolower($r['status'] ?? '')) {
                              'aprobat' => 'badge-success',
                              'respins' => 'badge-danger',
                              default   => 'badge-warning',
                          };
                        ?>
                        <span class="badge <?= $badge ?>"><?= htmlspecialchars($r['status']) ?></span>
                      </td>
                      <td>
                        <?php if (strcasecmp($r['status'], 'aprobat') === 0 && empty($r['factura_pdf_path'])): ?>
                          <form method="post" action="actions/genereaza_factura.php">
                            <input type="hidden" name="pontaj_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="redirect" value="rapoarte.php">
                            <button type="submit" class="btn btn-success btn-sm">Generează Factură</button>
                          </form>
                        <?php elseif (!empty($r['factura_pdf_path'])): ?>
                          <a href="<?= htmlspecialchars($r['factura_pdf_path']) ?>"
                             target="_blank" class="btn btn-primary btn-sm">Vezi Factură</a>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr class="font-weight-bold">
                    <td colspan="3" class="text-right">TOTAL:</td>
                    <td><?= number_format($rc_total_zile, 2) ?> zile</td>
                    <td colspan="3"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-info">Nu există pontaje pentru luna și clientul selectat.</div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- RAPORT LUNAR COLABORATOR                                     -->
    <!-- ============================================================ -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Raport lunar pe colaborator</h3>
      </div>
      <div class="card-body">

        <form method="GET" action="rapoarte.php">
          <!-- Pastram parametrii celorlalte sectiuni -->
          <input type="hidden" name="data_start"  value="<?= htmlspecialchars($f_data_start) ?>">
          <input type="hidden" name="data_end"    value="<?= htmlspecialchars($f_data_end) ?>">
          <input type="hidden" name="status"      value="<?= htmlspecialchars($f_status) ?>">
          <input type="hidden" name="client"      value="<?= htmlspecialchars($f_client) ?>">
          <input type="hidden" name="colaborator" value="<?= htmlspecialchars($f_colaborator) ?>">
          <input type="hidden" name="luna"        value="<?= htmlspecialchars($f_luna) ?>">
          <input type="hidden" name="rc_luna"     value="<?= htmlspecialchars($rc_luna) ?>">
          <input type="hidden" name="rc_client"   value="<?= htmlspecialchars($rc_client) ?>">

          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label>Luna</label>
              <input type="month" name="rl_luna" class="form-control"
                     value="<?= htmlspecialchars($rl_luna) ?>" required>
            </div>
            <div class="col-md-3">
              <label>Colaborator</label>
              <select name="rl_colab" class="form-control">
                <option value="">— Selectați —</option>
                <?php
                  $col2 = $mysqli->query("SELECT DISTINCT nume_prenume FROM pontaje ORDER BY nume_prenume ASC");
                  while ($co = $col2->fetch_assoc()):
                ?>
                  <option value="<?= htmlspecialchars($co['nume_prenume']) ?>"
                    <?= $rl_colab === $co['nume_prenume'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($co['nume_prenume']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6 d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search mr-1"></i> Generează
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm"
                      onclick="exportTable('tabelColab','csv')">CSV</button>
              <button type="button" class="btn btn-outline-secondary btn-sm"
                      onclick="exportTable('tabelColab','excel')">Excel</button>
            </div>
          </div>
        </form>

        <?php if ($rl_luna !== '' && $rl_colab !== ''): ?>
          <hr>
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h4><?= number_format($rl_total_zile, 2) ?></h4>
                  <p>Total zile facturate</p>
                </div>
                <div class="icon"><i class="fas fa-calendar"></i></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="small-box bg-primary">
                <div class="inner">
                  <h4><?= count($rl_rows) ?></h4>
                  <p>Înregistrări</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
              </div>
            </div>
          </div>

          <?php if (!empty($rl_rows)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-sm" id="tabelColab">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Luna facturare</th>
                    <th>Zile facturate</th>
                    <th>Factură</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rl_rows as $r): ?>
                    <tr>
                      <td>#<?= $r['id'] ?></td>
                      <td><?= htmlspecialchars($r['nume_client']) ?></td>
                      <td><?= htmlspecialchars($r['luna_facturare']) ?></td>
                      <td><?= $r['zile_facturate'] ?></td>
                      <td><?= htmlspecialchars($r['serie_factura']) ?> <?= htmlspecialchars($r['numar_factura']) ?></td>
                      <td>
                        <?php
                          $badge = match(strtolower($r['status'] ?? '')) {
                              'aprobat' => 'badge-success',
                              'respins' => 'badge-danger',
                              default   => 'badge-warning',
                          };
                        ?>
                        <span class="badge <?= $badge ?>"><?= htmlspecialchars($r['status']) ?></span>
                      </td>
                      <td>
                        <?php if (strcasecmp($r['status'], 'aprobat') === 0 && empty($r['factura_pdf_path'])): ?>
                          <form method="post" action="actions/genereaza_factura.php">
                            <input type="hidden" name="pontaj_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="redirect" value="rapoarte.php">
                            <button type="submit" class="btn btn-success btn-sm">Generează Factură</button>
                          </form>
                        <?php elseif (!empty($r['factura_pdf_path'])): ?>
                          <a href="<?= htmlspecialchars(rawurlencode(basename($r['factura_pdf_path']))) ?>"
                             target="_blank" class="btn btn-primary btn-sm">Vezi Factură</a>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr class="font-weight-bold">
                    <td colspan="3" class="text-right">TOTAL:</td>
                    <td><?= number_format($rl_total_zile, 2) ?> zile</td>
                    <td colspan="3"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-info">Nu există pontaje pentru luna și colaboratorul selectat.</div>
          <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>

  </div><!-- /.content-wrapper -->
</div><!-- /.wrapper -->

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
$(document).ready(function () {

    // DataTable – Raport General
    if ($('#tabelGeneral tbody tr').length > 0) {
        $('#tabelGeneral').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/ro.json'
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'csv',   text: 'CSV',   className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'excel', text: 'Excel', className: 'btn btn-sm btn-outline-secondary' }
            ]
        });
    }

});

// Export simplu pentru tabele din rapoarte client/colab
function exportTable(tableId, format) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let rows = [];
    for (let row of table.rows) {
        let cells = [];
        for (let cell of row.cells) {
            cells.push('"' + cell.innerText.replace(/"/g, '""').trim() + '"');
        }
        rows.push(cells.join(','));
    }

    const csvContent = rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = tableId + '_export.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

</body>
</html>
