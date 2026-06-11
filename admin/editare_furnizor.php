<?php

$conn = new mysqli("localhost", "root", "", "2web_pontaj");

if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("ID lipsă");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM furnizori WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$furnizor = $result->fetch_assoc();

if (!$furnizor) {
    die("Furnizor inexistent");
}

/* UPDATE */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nume    = $_POST['nume'];
    $email   = $_POST['email'];
    $telefon = $_POST['telefon'];
    $adresa  = $_POST['adresa'];
    $cui     = $_POST['cui'];
    $activ   = isset($_POST['activ']) ? 1 : 0;

    $update = $conn->prepare("
        UPDATE furnizori
        SET 
            nume=?,
            email=?,
            telefon=?,
            adresa=?,
            cui=?,
            activ=?,
            updated_at=NOW()
        WHERE id=?
    ");

    $update->bind_param(
        "sssssii",
        $nume,
        $email,
        $telefon,
        $adresa,
        $cui,
        $activ,
        $id
    );

    if ($update->execute()) {

        header("Location: furnizori.php");
        exit;

    } else {

        $error = "Eroare actualizare: " . $update->error;
    }
}

?>

<!DOCTYPE html>
<html lang="ro">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editare Furnizor</title>

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Bootstrap 4 -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- AdminLTE 3 -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

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

            <div class="container-fluid">

                <div class="row">

                    <div class="col-lg-8">

                        <div class="card card-warning card-outline">

                            <div class="card-header">

                                <h3 class="card-title">
                                    <i class="fas fa-edit"></i>
                                    Editare Furnizor
                                </h3>

                            </div>

                            <?php if(isset($error)) { ?>

                                <div class="alert alert-danger m-3">
                                    <?= $error ?>
                                </div>

                            <?php } ?>

                            <form method="POST">

                                <div class="card-body">

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Nume Furnizor
                                        </label>

                                        <input type="text"
                                            name="nume"
                                            class="form-control"
                                            value="<?= htmlspecialchars($furnizor['nume']) ?>"
                                            required>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Email
                                        </label>

                                        <input type="email"
                                            name="email"
                                            class="form-control"
                                            value="<?= htmlspecialchars($furnizor['email']) ?>">

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Telefon
                                        </label>

                                        <input type="text"
                                            name="telefon"
                                            class="form-control"
                                            value="<?= htmlspecialchars($furnizor['telefon']) ?>">

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Adresă
                                        </label>

                                        <textarea
                                            name="adresa"
                                            class="form-control"
                                            rows="3"><?= htmlspecialchars($furnizor['adresa']) ?></textarea>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            CUI
                                        </label>

                                        <input type="text"
                                            name="cui"
                                            class="form-control"
                                            value="<?= htmlspecialchars($furnizor['cui']) ?>">

                                    </div>

                                    <div class="form-check form-switch">

                                        <input class="form-check-input"
                                            type="checkbox"
                                            id="activ"
                                            name="activ"
                                            <?= $furnizor['activ'] ? 'checked' : '' ?>>

                                        <label class="form-check-label" for="activ">
                                            Furnizor Activ
                                        </label>

                                    </div>

                                </div>

                                <div class="card-footer">

                                    <button type="submit"
                                        class="btn btn-warning">

                                        <i class="fas fa-save"></i>
                                        Actualizează

                                    </button>

                                    <a href="setari.php"
                                        class="btn btn-secondary">

                                        <i class="fas fa-arrow-left"></i>
                                        Înapoi

                                    </a>

                                </div>

                            </form>

                        </div>

                    </div>

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