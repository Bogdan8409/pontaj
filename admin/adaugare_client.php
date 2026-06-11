<?php
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

if ($conn->connect_error) {
    die("Conexiune esuata: " . $conn->connect_error);
}

$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nume = $_POST['nume'];
    $cui = $_POST['cui'];
    $reg_com = $_POST['reg_com'];
    $adresa = $_POST['adresa'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];
    $banca = $_POST['banca'];
    $iban = $_POST['iban'];
    $activ = isset($_POST['activ']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO clienti 
    (nume,cui,reg_com,adresa,telefon,email,banca,iban,activ,created_at) 
    VALUES (?,?,?,?,?,?,?,?,?,NOW())");

    $stmt->bind_param(
        "ssssssssi",
        $nume,
        $cui,
        $reg_com,
        $adresa,
        $telefon,
        $email,
        $banca,
        $iban,
        $activ
    );

    if ($stmt->execute()) {
        $mesaj = "<div class='alert alert-success'>Client adaugat cu succes!</div>";
    } else {
        $mesaj = "<div class='alert alert-danger'>Eroare: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>

    <meta charset="UTF-8">
    <title>Adauga Client</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

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

            <div class="content-header">
                <div class="container-fluid">
                    <h3 class="mb-3">Adauga Client</h3>
                </div>
            </div>

            <div class="content">

                <div class="container-fluid">

                    <?php echo $mesaj; ?>

                    <div class="card card-primary">

                        <div class="card-header">
                            <h3 class="card-title">Formular Client</h3>
                        </div>

                        <form method="POST">

                            <div class="card-body">

                                <div class="form-group">
                                    <label>Nume Firma</label>
                                    <input type="text" name="nume" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>CUI</label>
                                    <input type="text" name="cui" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Registrul Comertului</label>
                                    <input type="text" name="reg_com" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Adresa</label>
                                    <input type="text" name="adresa" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Telefon</label>
                                    <input type="text" name="telefon" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Banca</label>
                                    <input type="text" name="banca" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>IBAN</label>
                                    <input type="text" name="iban" class="form-control">
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="activ" name="activ"
                                            checked>
                                        <label class="custom-control-label" for="activ">Client Activ</label>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">

                                <button type="submit" class="btn btn-success">
                                    Salveaza Client
                                </button>

                                <a href="setari.php" class="btn btn-secondary">
                                    Anuleaza
                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>

</html>