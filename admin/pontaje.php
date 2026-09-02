<?php
$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) {
    die("Eroare conexiune DB: " . $mysqli->connect_error);
}

$sql = "
SELECT p.*, c.nume AS client_nume
FROM pontaje p
LEFT JOIN clienti c ON p.client_id = c.id
ORDER BY p.data_trimitere DESC
";

$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="utf-8">
    <title>Pontaje</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
</head>

<body class="hold-transition sidebar-mini">

    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#">
                        <i class="fas fa-bars"></i>
                    </a>
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
                        <li class="nav-item">
                            <a href="index.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pontaje.php" class="nav-link active">
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

        <div class="content-wrapper">

            <section class="content-header">
                <div class="container-fluid">
                    <h1>Pontaje</h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">

                    <!-- Mesaje feedback dupa actiuni -->
                    <?php
                    $mesaje = [
                        'factura_generata' => ['success', 'Factura a fost generată și salvată cu succes!'],
                        'pontaj_aprobat'   => ['success', 'Pontajul a fost aprobat cu succes!'],
                        'pontaj_respins'   => ['warning', 'Pontajul a fost respins.'],
                        'pontaj_resetat'   => ['info',    'Pontajul a fost resetat la Pending.'],
                        'id_invalid'       => ['danger',  'ID pontaj invalid.'],
                        'aprobare_esuata'  => ['danger',  'Eroare la aprobare. Încearcați din nou.'],
                        'respingere_esuata'=> ['danger',  'Eroare la respingere. Încearcați din nou.'],
                        'resetare_esuata'  => ['danger',  'Eroare la resetare. Încearcați din nou.'],
                        'motiv_lipsa'      => ['danger',  'Motivul respingerii este obligatoriu.'],
                    ];
                    $succes = $_GET['succes'] ?? '';
                    $eroare = $_GET['eroare'] ?? '';
                    $cheie  = $succes ?: $eroare;
                    if ($cheie && isset($mesaje[$cheie])):
                        [$tip, $text] = $mesaje[$cheie];
                    ?>
                    <div class="alert alert-<?= $tip ?> alert-dismissible fade show mx-3" role="alert">
                        <i class="fas fa-<?= $tip === 'success' ? 'check-circle' : ($tip === 'danger' ? 'exclamation-circle' : 'info-circle') ?> mr-1"></i>
                        <?= $text ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista Pontaje</h3>
                        </div>

                        <div class="card-body">
                            <table id="pontajeTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>Data Trimitere</th>
                                        <th>Colaborator</th>
                                        <th>Luna Facturare</th>
                                        <th>Client</th>
                                        <th>Zile Facturate</th>
                                        <th>Info</th>
                                        <th>Status</th>
                                        <th>Factură</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()):
                                        $status       = strtolower(trim($row['status'] ?? ''));
                                        $are_factura  = !empty($row['factura_pdf_path']);
                                        $este_aprobat = $status === 'aprobat';
                                        $este_pending = $status === 'pending';
                                        $este_respins = $status === 'respins';

                                        $badge_cls = match($status) {
                                            'aprobat' => 'badge-success',
                                            'respins' => 'badge-danger',
                                            default   => 'badge-warning',
                                        };
                                    ?>
                                        <tr>
                                            <!-- ID -->
                                            <td><?= htmlspecialchars($row['id']) ?></td>

                                            <!-- Data trimitere -->
                                            <td><?= htmlspecialchars($row['data_trimitere']) ?></td>

                                            <!-- Colaborator -->
                                            <td><?= htmlspecialchars($row['nume_prenume'] ?? '-') ?></td>

                                            <!-- Luna facturare -->
                                            <td><?= htmlspecialchars($row['luna_facturare'] ?? '-') ?></td>

                                            <!-- Client -->
                                            <td><?= htmlspecialchars($row['client_nume'] ?? '-') ?></td>

                                            <!-- Zile facturate -->
                                            <td><?= htmlspecialchars($row['zile_facturate']) ?></td>
                                            <!-- Info -->
                                             <td><?php
                                             if ($row['motiv_respingere'] !== null){

                                             
                                             echo substr($row['motiv_respingere'], 0, 20) . '...'; };?>
                                             
                                             </td>

                                            <!-- Status -->
                                            <td>
                                                <span class="badge <?= $badge_cls ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                                <?php if ($este_respins && !empty($row['motiv_respingere'])): ?>
                                                    <br>
                                                    <!-- <large class="text-danger" title="<?= htmlspecialchars($row['motiv_respingere']) ?>">
                                                        
                                                        
                                                    </large> -->
                                                    <div class="hover">
                                                        <div class="hover_icon"><i class="fas fa-info-circle"></i></div>
                                                        <div class="hover_text"><?= htmlspecialchars(($row['motiv_respingere'])) ?></div>
                                                    </div>
                                                    <style>
                                                        .hover {
                                                            position: relative;
                                                            display: inline-block;
                                                        }
                                                        .hover_icon {
                                                            color: #dc3545;
                                                            cursor: pointer;
                                                        }
                                                        .hover_text {
                                                            visibility: hidden;
                                                            width: 200px;
                                                            background-color: #f8d7da;
                                                            color: #721c24;
                                                            text-align: left;
                                                            border-radius: 4px;
                                                            padding: 8px;
                                                            position: absolute;
                                                            z-index: 1;
                                                            bottom: 125%;
                                                            left: 50%;
                                                            margin-left: -100px;
                                                            opacity: 0;
                                                            transition: opacity 0.3s;
                                                        }
                                                        .hover:hover .hover_text {
                                                            visibility: visible;
                                                            opacity: 1;
                                                        }
                                                    </style>
                                                    
                                                <?php endif; ?>
                                            </td>

                                            <!-- Factura PDF -->
                                            <td>
                                                <?php if ($are_factura): ?>
                                                    <a href="../facturi/<?= htmlspecialchars(rawurlencode(basename($row['factura_pdf_path']))) ?>"
                                                       target="_blank"
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-file-pdf"></i> Vezi
                                                    </a>
                                                    <a href="../facturi/<?= htmlspecialchars(rawurlencode(basename($row['factura_pdf_path']))) ?>"
                                                       download="<?= htmlspecialchars(basename($row['factura_pdf_path'])) ?>"
                                                       class="btn btn-outline-primary btn-sm"
                                                       title="Descarcă">
                                                        <i class="fas fa-download"></i>
                                                    </a>

                                                    <br class="d-none d-md-block">

                                                    <?php if (!empty($row['xml_path'])): ?>
                                                        <a href="../<?= htmlspecialchars($row['xml_path']) ?>"
                                                           target="_blank"
                                                           class="btn btn-success btn-sm mt-1"
                                                           title="XML e-Factura generat">
                                                            <i class="fas fa-file-code"></i> XML
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="../EFacturaXML.php?id=<?= (int)$row['id'] ?>"
                                                           target="_blank"
                                                           class="btn btn-outline-success btn-sm mt-1"
                                                           title="Generează XML e-Factura">
                                                            <i class="fas fa-file-code"></i> Generează XML
                                                        </a>
                                                    <?php endif; ?>

                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Lipsă</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Actiuni -->
                                            <td>
                                                <div class="d-flex flex-wrap" style="gap:4px;">

                                                    <?php if ($este_pending): ?>
                                                        <!-- Aprobare -->
                                                        <form method="post" action="actions/aprobare_pontaj.php">
                                                            <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                                                            <!-- <input type="hidden" name="redirect"  value="pontaje.php"> -->
                                                            <button type="submit"
                                                                    class="btn btn-success btn-sm"
                                                                    title="Aprobă"
                                                                    onclick="return confirm('Aprobați pontajul #<?= $row['id'] ?>?')">
                                                                <i class="fas fa-check"></i> Aprobă
                                                            </button>
                                                        </form>

                                                        <!-- Respingere -->
                                                        <button type="button"
                                                                class="btn btn-danger btn-sm btn-respinge"
                                                                title="Respinge"
                                                                data-toggle="modal"
                                                                data-target="#modalRespingere"
                                                                data-id="<?= htmlspecialchars($row['id']) ?>"
                                                                data-colaborator="<?= htmlspecialchars($row['nume_prenume'] ?? '') ?>">
                                                            <i class="fas fa-times"></i> Respinge
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($este_aprobat && !$are_factura): ?>
                                                        <!-- Genereaza factura -->
                                                        <form method="post" action="actions/genereaza_factura.php">
                                                            <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="redirect"  value="pontaje.php">
                                                            <button type="submit"
                                                                    class="btn btn-success btn-sm"
                                                                    title="Generează factură PDF">
                                                                <i class="fas fa-file-invoice"></i> Generează
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($este_respins): ?>
                                                        <!-- Resetare la Pending -->
                                                        <form method="post" action="actions/reseteaza_pontaj.php">
                                                            <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="redirect"  value="../pontaje.php">
                                                            <button type="submit"
                                                                    class="btn btn-warning btn-sm"
                                                                    title="Resetează la Pending"
                                                                    onclick="return confirm('Resetați pontajul #<?= $row['id'] ?> la Pending?')">
                                                                <i class="fas fa-redo"></i> Resetează
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <!-- Detalii -->
                                                    <a href="./pontaj/detalii_pontaj.php?id=<?= $row['id'] ?>"
                                                       class="btn btn-info btn-sm"
                                                       title="Vezi detalii">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                </div>
                                            </td>

                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <footer class="main-footer text-center">
            2WEB SOFTWARE SRL
        </footer>

    </div>

    <!-- Modal Respingere -->
    <div class="modal fade" id="modalRespingere" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="./actions/respinge_pontaj.php">

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-times-circle mr-1"></i> Respingere Pontaj
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="pontaj_id" id="modalPontajId">
                        <input type="hidden" name="redirect"  value="../pontaje.php">

                        <p>
                            Respingeți pontajul colaboratorului
                            <strong id="modalColaborator"></strong>?
                        </p>

                        <div class="form-group">
                            <label for="motivRespingere">
                                Motiv respingere <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control"
                                      id="motivRespingere"
                                      name="motiv_respingere"
                                      rows="3"
                                      placeholder="Explicați motivul respingerii..."
                                      required></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Anulează
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times mr-1"></i> Respinge
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function () {

            // DataTable
            $("#pontajeTable").DataTable({
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/ro.json'
                },
                columnDefs: [
                    // Dezactiveaza sortarea pe coloana Actiuni
                    { orderable: false, targets: 8 }
                ]
            });

            // Populeaza modalul de respingere
            $('#modalRespingere').on('show.bs.modal', function (event) {
                var btn = $(event.relatedTarget);
                $('#modalPontajId').val(btn.data('id'));
                $('#modalColaborator').text(btn.data('colaborator'));
                $('#motivRespingere').val('');
            });

        });
    </script>

</body>
</html>
