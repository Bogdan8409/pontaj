<?php

// Conexiune la baza de date
$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) {
    die("Eroare conectare DB: " . $mysqli->connect_error);
}
$pontaje_result = $mysqli->query("SELECT * FROM pontaje ORDER BY data_trimitere DESC");
// Preluare clienti
$sql = "SELECT id, nume FROM clienti ORDER BY nume ASC";
$clienti_result = $mysqli->query($sql);

$sql = "SELECT 
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
        ORDER BY p.data_trimitere DESC";

$result = $mysqli->query($sql);
$pontaj_id = $pontaj['id'] ?? 0;

// Preluare furnizori
$furnizori_sql = "SELECT id, nume, email, telefon, adresa, cui, activ FROM furnizori ORDER BY id DESC";
$furnizori_result = $mysqli->query($furnizori_sql);





if ($mysqli->connect_error) {
    die("Conexiune esuata: " . $mysqli->connect_error);
}

$sql = "SELECT * FROM clienti ORDER BY id DESC";
$result = $mysqli->query($sql);


?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="utf-8">
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
        <div class="content-wrapper p-4">


            <h2 class="mb-4">Setări și Configurări</h2>

            <!-- ===================== -->
            <!-- 1. SETARI EMAIL -->
            <!-- ===================== -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Setări Email</h3>
                </div>
                <div class="card-body row g-3">

                    <div class="col-md-6">
                        <label>Adrese email administratori</label>
                        <input type="text" class="form-control" placeholder="admin@site.ro, office@site.ro">
                    </div>

                    <div class="col-md-6">
                        <label>Server SMTP</label>
                        <input type="text" class="form-control" placeholder="smtp.site.ro">
                    </div>

                    <div class="col-md-3">
                        <label>Port SMTP</label>
                        <input type="number" class="form-control" placeholder="587">
                    </div>

                    <div class="col-md-3">
                        <label>User SMTP</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Parolă SMTP</label>
                        <input type="password" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Criptare</label>
                        <select class="form-control">
                            <option>TLS</option>
                            <option>SSL</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label>Template Email</label>
                        <textarea class="form-control" rows="4">
Bună {{nume}},

Pontajul tău a fost aprobat.
Factura atașată.

Mulțumim!
            </textarea>
                    </div>

                    <div class="col-md-12">
                        <button class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- 2. SETARI CLIENTI -->
            <!-- ===================== -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">Setări Clienți</h3>
                    <!-- <button class="btn btn-success btn-sm">Adaugă Client</button> -->
                    <a href="adaugare_client.php" class="btn btn-success btn-sm">Adaugă Client</a>
                </div>
                <div class="card-body">


                    <table id="clientiTable" class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Nume</th>
                                <th>Adresa</th>
                                <th>Info Factura</th>
                                <th>Status</th>
                                <th>Actiuni</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($result->num_rows > 0): ?>

                                <?php while ($row = $result->fetch_assoc()): ?>

                                    <tr>

                                        <td><?= $row['id'] ?></td>

                                        <td>
                                            <strong><?= htmlspecialchars($row['nume']) ?></strong><br>
                                            <small><?= htmlspecialchars($row['email']) ?></small>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['adresa']) ?><br>
                                            <small><?= htmlspecialchars($row['telefon']) ?></small>
                                        </td>

                                        <td>
                                            CUI: <?= htmlspecialchars($row['cui']) ?><br>
                                            Reg: <?= htmlspecialchars($row['reg_com']) ?><br>
                                            IBAN: <?= htmlspecialchars($row['iban']) ?>
                                        </td>

                                        <td>

                                            <?php if ($row['activ'] == 1): ?>

                                                <span class="badge badge-success">Activ</span>

                                            <?php else: ?>

                                                <span class="badge badge-danger">Inactiv</span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <a href="editare_client.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                                Editeaza
                                            </a>

                                            <a href="sterge_client.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Sigur vrei sa stergi clientul?')">

                                                Sterge

                                            </a>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center">Nu exista clienti</td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- ===================== -->
            <!-- 3. SETARI FURNIZORI -->
            <!-- ===================== -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">Setări Furnizori</h3>
                    <a href="adaugare_furnizor.php" class="btn btn-success btn-sm">Adaugă Furnizor</a>
                </div>
                <div class="card-body">

                    <table id="furnizoriTable" class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Nume</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Adresă</th>
                                <th>CUI</th>
                                <th>Status</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($furnizori_result && $furnizori_result->num_rows > 0): ?>

                                <?php while ($row = $furnizori_result->fetch_assoc()): ?>

                                    <tr>

                                        <td><?= $row['id'] ?></td>

                                        <td>
                                            <strong><?= htmlspecialchars($row['nume']) ?></strong>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['email']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['telefon']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['adresa']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['cui']) ?>
                                        </td>

                                        <td>

                                            <?php if ($row['activ'] == 1): ?>

                                                <span class="badge badge-success">Activ</span>

                                            <?php else: ?>

                                                <span class="badge badge-danger">Inactiv</span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <a href="editare_furnizor.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                                Editează
                                            </a>

                                            <a href="sterge_furnizor.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Sigur vrei să ștergi furnizorul?')">

                                                Șterge

                                            </a>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="text-center">Nu exista furnizori</td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- ===================== -->
            <!-- 4. SETARI FACTURA -->
            <!-- ===================== -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Setări Factură</h3>
                </div>
                <div class="card-body row g-3">

                    <div class="col-md-4">
                        <label>Logo companie</label>
                        <input type="file" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Nume companie</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>CUI</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Adresă</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Bancă</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>IBAN</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Serie factură</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Număr pornire</label>
                        <input type="number" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Template PDF Factură</label>
                        <textarea class="form-control" rows="4"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label>Opțiuni formatare</label>
                        <select class="form-control">
                            <option>Standard</option>
                            <option>Compact</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <button class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- 5. SETARI ADMINI -->
            <!-- ===================== -->
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">Setări Utilizatori Admin</h3>
                    <!-- <button class="btn btn-success btn-sm">Adaugă Admin</button> -->
                    <a href="./actions/adauga_admin.php" class="btn btn-success btn-sm">Adaugă Admin</a>
                </div>
                <div class="card-body">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nume</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $conn = new mysqli("localhost", "root", "", "2web_pontaj");

                            if ($conn->connect_error) {
                                die("Eroare conexiune: " . $conn->connect_error);
                            }

                            $result = $conn->query("SELECT id, nume_complet, email, activ FROM admini");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {

                                    $rol = ($row['activ'] == 1) ? "Activ" : "Inactiv";

                                    echo "<tr>
                <td>{$row['nume_complet']}</td>
                <td>{$row['email']}</td>
                <td>{$rol}</td>
                <td>
                    <a href='./actions/edit_admin.php?id={$row['id']}' class='btn btn-sm btn-warning'>Editează</a>
                    <a href='./actions/sterge_admin.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Sigur ștergi acest admin?')\">Șterge</a>
                </td>
              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Nu există admini</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>

        $(document).ready(function () {

            $('#clientiTable').DataTable({

                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100],

                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/ro.json"
                }

            });

        });

    </script>
</body>

</html>