<?php

// Conectare MySQL
$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");

if ($mysqli->connect_error) {
    die("Conexiune eșuată: " . $mysqli->connect_error);
}

// Salvare formular
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nume = $_POST['nume'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $adresa = $_POST['adresa'];
    $cui = $_POST['cui'];
    $activ = isset($_POST['activ']) ? 1 : 0;

    // INSERT
    $sql = "INSERT INTO furnizori 
            (nume, email, telefon, adresa, cui, activ)
            VALUES 
            (?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(
        "sssssi",
        $nume,
        $email,
        $telefon,
        $adresa,
        $cui,
        $activ
    );

    if ($stmt->execute()) {
        $success = "Furnizor adăugat cu succes!";
    } else {
        $error = "Eroare: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaugă Furnizor</title>

    <!-- Bootstrap -->
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
                    <h1>Adaugă Furnizor</h1>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php } ?>

                    <div class="card card-primary">

                        <div class="card-header">
                            <h3 class="card-title">
                                Formular Furnizor
                            </h3>
                        </div>

                        <form method="POST">

                            <div class="card-body">

                                <div class="form-group">
                                    <label>Nume</label>
                                    <input type="text" name="nume" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Telefon</label>
                                    <input type="text" name="telefon" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Adresă</label>
                                    <textarea name="adresa" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>CUI</label>
                                    <input type="text" name="cui" class="form-control">
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" name="activ" class="form-check-input" id="activ" checked>

                                    <label class="form-check-label" for="activ">
                                        Activ
                                    </label>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Salvează
                                </button>
                            </div>

                        </form>

                    </div>

                </div>
            </section>

        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AdminLTE -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>

</html>