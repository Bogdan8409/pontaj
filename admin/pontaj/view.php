<?php
session_start();

// ── Verifică admin autentificat ──────────────────────────────────────────────
if (empty($_SESSION['admin_id'])) {
  header('Location: /admin/login.php');
  exit;
}

// ── Validare ID ──────────────────────────────────────────────────────────────
$idPontaj = $_GET['id'] ?? null;
if (!$idPontaj || !ctype_digit((string) $idPontaj)) {
  die('Pontaj invalid');
}
$idPontaj = (int) $idPontaj;   // lucrăm cu int, nu string

// ── Conexiune BD ─────────────────────────────────────────────────────────────
$mysqli = new mysqli('localhost', 'root', '', '2web_pontaj');
if ($mysqli->connect_errno) {
  die('Eroare conexiune BD: ' . $mysqli->connect_error);
}

// ── Date pontaj ──────────────────────────────────────────────────────────────
$stmt = $mysqli->prepare(
  'SELECT p.id, p.luna_facturare, p.nume_prenume, p.zile_rezervate,
            p.zile_facturate, p.nr_total_zile, p.client_id,
            p.serie_factura, p.numar_factura,
            p.timesheet_path, p.timesheet_original_name,
            p.status, p.motiv_respingere,
            p.data_trimitere, p.data_procesare,
            p.factura_pdf_path, p.email_colaborator,
            p.created_at, p.updated_at,
            c.nume AS client_nume
     FROM   pontaje p
     LEFT JOIN clienti c ON p.client_id = c.id
     WHERE  p.id = ?'
);
$stmt->bind_param('i', $idPontaj);
$stmt->execute();
$pontaj = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pontaj) {
  $mysqli->close();
  die('Pontaj inexistent');
}

// ── Istoric din BD ───────────────────────────────────────────────────────────
// $istoric = [];
// $stmtI = $mysqli->prepare(
//     'SELECT data_actiune, actiune, utilizator
//      FROM   istoric_pontaje
//      WHERE  pontaj_id = ?
//      ORDER BY data_actiune ASC'
// );
// if ($stmtI) {                   // tabelul poate lipsi în dev → fallback sigur
//     $stmtI->bind_param('i', $idPontaj);
//     $stmtI->execute();
//     $resI = $stmtI->get_result();
//     while ($row = $resI->fetch_assoc()) {
//         $istoric[] = $row;
//     }
//     $stmtI->close();
// }

// $mysqli->close();

// Helper: escape scurt
function e(mixed $v): string
{
  return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="utf-8">
  <title>Detalii Pontaj #<?= e($pontaj['id']) ?></title>
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
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="/admin/index.php" class="brand-link">
        <span class="brand-text font-weight-light">Gestionare Pontaje</span>
      </a>
      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="../index.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="../pontaje.php" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Pontaje</p></a></li>
          <li class="nav-item"><a href="../rapoarte.php" class="nav-link active"><i class="nav-icon fas fa-chart-bar"></i><p>Rapoarte</p></a></li>
          <li class="nav-item"><a href="../setari.php" class="nav-link"><i class="nav-icon fas fa-cog"></i><p>Setări</p></a></li>
        </ul>
        </nav>
      </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper p-4">
      <section class="content-header">
        <div class="container-fluid">
          <h2>Pontaj #<?= e($pontaj['id']) ?></h2>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">

          <!-- INFO PONTAJ -->
          <div class="card">
            <div class="card-header"><b>Informații pontaj</b></div>
            <div class="card-body">
              <p><b>Colaborator:</b> <?= e($pontaj['nume_prenume']) ?></p>
              <p><b>Email colaborator:</b> <?= e($pontaj['email_colaborator']) ?></p>
              <p><b>Client:</b> <?= e($pontaj['client_nume'] ?? $pontaj['client_id']) ?></p>
              <p><b>Luna facturare:</b> <?= e($pontaj['luna_facturare']) ?></p>
              <p><b>Zile rezervate:</b> <?= e($pontaj['zile_rezervate']) ?></p>
              <p><b>Zile facturate:</b> <?= e($pontaj['zile_facturate']) ?></p>
              <p><b>Nr. total zile:</b> <?= e($pontaj['nr_total_zile']) ?></p>
              <?php if ($pontaj['serie_factura'] || $pontaj['numar_factura']): ?>
                <p><b>Factură:</b> <?= e($pontaj['serie_factura']) ?><?= e($pontaj['numar_factura']) ?></p>
              <?php endif; ?>
              <p><b>Status:</b>
                <?php match ($pontaj['status']) {
                  'pending' => print ('<span class="badge badge-warning">În așteptare</span>'),
                  'aprobat' => print ('<span class="badge badge-success">Aprobat</span>'),
                  default => print ('<span class="badge badge-danger">Respins</span>'),
                }; ?>
              </p>
              <?php if ($pontaj['status'] === 'respins' && $pontaj['motiv_respingere']): ?>
                <p><b>Motiv respingere:</b> <?= e($pontaj['motiv_respingere']) ?></p>
              <?php endif; ?>
              <p><b>Data trimiterii:</b> <?= e($pontaj['data_trimitere']) ?></p>
              <?php if ($pontaj['data_procesare']): ?>
                <p><b>Data procesării:</b> <?= e($pontaj['data_procesare']) ?></p>
              <?php endif; ?>

              <!-- Timesheet -->
              <p><b>Timesheet:</b>
                <?php if (!empty($pontaj['timesheet_path'])): ?>
                  <a href="../../uploads/timesheets/<?= e($pontaj['timesheet_path']) ?>" target="_blank" rel="noopener noreferrer"
                    class="btn btn-sm btn-info">
                    <i class="fas fa-file-download"></i>
                    <?= e($pontaj['timesheet_original_name'] ?: 'Preview / Download') ?>
                  </a>
                <?php else: ?>
                  <span class="text-muted">Timesheet indisponibil</span>
                <?php endif; ?>
              </p>
        
              <!-- Factură PDF -->
              <?php if (!empty($pontaj['factura_pdf_path'])): ?>
                <p><b>Factură PDF:</b>
                  <a href="../../facturi/<?= e(rawurlencode(basename($pontaj['factura_pdf_path']))) ?>" target="_blank"
                    rel="noopener noreferrer" class="btn btn-sm btn-secondary">
                    <i class="fas fa-file-pdf"></i> Deschide factură
                  </a>
                </p>
              <?php endif; ?>
            </div>
          </div>

          <!-- ACȚIUNI -->
          <?php if (in_array($pontaj['status'], ['pending', 'aprobat'], true)): ?>
            <div class="card">
              <div class="card-header"><b>Acțiuni</b></div>
              <div class="card-body d-flex gap-2" style="gap:.5rem">

                <?php if ($pontaj['status'] === 'pending'): ?>
                  <a href="/admin/actions/aproba_pontaj.php?id=<?= e($pontaj['id']) ?>"
                    onclick="return confirm('Sigur doriți să aprobați acest pontaj?')" class="btn btn-success">
                    <i class="fas fa-check"></i> Aprobă Pontaj
                  </a>
                  <button class="btn btn-danger" data-toggle="modal" data-target="#modalRespinge">
                    <i class="fas fa-times"></i> Respinge Pontaj
                  </button>
                <?php endif; ?>

                <?php if ($pontaj['status'] === 'aprobat'): ?>
                  <a href="/admin/actions/regenereaza_factura.php?id=<?= e($pontaj['id']) ?>"
                    onclick="return confirm('Re-generezi factura?')" class="btn btn-warning">
                    <i class="fas fa-file-pdf"></i> Re-generează Factura
                  </a>
                <?php endif; ?>

              </div>
            </div>
          <?php endif; ?>

          <!-- ISTORIC -->
          <div class="card">
            <div class="card-header"><b>Istoric acțiuni</b></div>
            <div class="card-body">
              <?php if ($istoric): ?>
                <ul class="list-unstyled mb-0">
                  <?php foreach ($istoric as $i): ?>
                    <li>
                      <i class="fas fa-clock text-muted mr-1"></i>
                      <?= e($i['data_actiune']) ?> –
                      <?= e($i['actiune']) ?>
                      <span class="text-muted">(<?= e($i['utilizator']) ?>)</span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted mb-0">Nu există înregistrări în istoric.</p>
              <?php endif; ?>
            </div>
          </div>

        </div><!-- /.container-fluid -->
      </section>
    </div><!-- /.content-wrapper -->
  </div><!-- /.wrapper -->

  <!-- MODAL RESPINGERE -->
  <div class="modal fade" id="modalRespinge" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <form method="POST" action="/admin/actions/respinge_pontaj.php">
        <input type="hidden" name="id_pontaj" value="<?= e($pontaj['id']) ?>">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Respinge pontaj #<?= e($pontaj['id']) ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Închide">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <label for="motivRespingere">Motiv respingere <span class="text-danger">*</span></label>
            <textarea id="motivRespingere" name="motiv_respingere" class="form-control" minlength="10" rows="4"
              required></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Anulează</button>
            <button type="submit" class="btn btn-danger">
              <i class="fas fa-times"></i> Respinge
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>

</html>