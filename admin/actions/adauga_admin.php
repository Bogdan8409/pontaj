<?php
$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "2web_pontaj");

    if ($conn->connect_error) {
        die("Eroare conexiune: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nume = $_POST['nume_complet'];
    $activ = $_POST['activ'];

    $stmt = $conn->prepare("INSERT INTO admini (username, email, password_hash, nume_complet, activ) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $email, $password, $nume, $activ);

    if ($stmt->execute()) {
        $mesaj = '<div class="alert alert-success">Admin adăugat cu succes!</div>';
    } else {
        $mesaj = '<div class="alert alert-danger">Eroare: ' . $conn->error . '</div>';
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adăugare Admin</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .form-container {
            background: #fff;
        }
        .header-section {
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="form-container shadow rounded">

                <!-- Header -->
                <div class="header-section bg-primary text-white p-4">
                    <h3 class="mb-0">Adăugare Admin</h3>
                    <small>Completați datele pentru crearea unui cont</small>
                </div>

                <div class="p-4">

                    <!-- Mesaj -->
                    <?php echo $mesaj; ?>

                    <form method="POST">

                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>

                        <!-- Parola -->
                        <div class="mb-3">
                            <label class="form-label">Parolă</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <!-- Nume complet -->
                        <div class="mb-3">
                            <label class="form-label">Nume complet</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="nume_complet" class="form-control" required>
                            </div>
                        </div>

                        <!-- Activ -->
                        <div class="mb-4">
                            <label class="form-label">Status cont</label>
                            <select name="activ" class="form-select">
                                <option value="1">Activ</option>
                                <option value="0">Inactiv</option>
                            </select>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-2"></i>Adaugă Admin
                        </button>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>