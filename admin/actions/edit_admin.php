<?php
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

if ($conn->connect_error) {
    die("Eroare conexiune: " . $conn->connect_error);
}

$mesaj = "";

/* ======================
   UPDATE (cand trimiti formularul)
====================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nume = $_POST['nume_complet'];
    $activ = $_POST['activ'];

    // daca parola NU e completata → nu o modificam
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE admini SET username=?, email=?, password_hash=?, nume_complet=?, activ=? WHERE id=?");
        $stmt->bind_param("ssssii", $username, $email, $password, $nume, $activ, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admini SET username=?, email=?, nume_complet=?, activ=? WHERE id=?");
        $stmt->bind_param("sssii", $username, $email, $nume, $activ, $id);
    }

    if ($stmt->execute()) {
        $mesaj = '<div class="alert alert-success">Admin actualizat!</div>';
        header("Location: ../setari.php?succes=admin_actualizat&id={$id}");
    } else {
        $mesaj = '<div class="alert alert-danger">Eroare update!</div>';
    }
}

/* ======================
   PRELUARE DATE
====================== */
$id = $_GET['id'];

$result = $conn->query("SELECT * FROM admini WHERE id = $id");

if ($result->num_rows == 0) {
    die("Admin inexistent");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Editare Admin</h4>
                    </div>

                    <div class="card-body">

                        <?php echo $mesaj; ?>

                        <form method="POST">

                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                            <!-- Username -->
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control"
                                        value="<?php echo $row['username']; ?>" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo $row['email']; ?>" required>
                                </div>
                            </div>

                            <!-- Parola -->
                            <div class="mb-3">
                                <label class="form-label">Parolă (lasă gol dacă nu vrei să o schimbi)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control">
                                </div>
                            </div>

                            <!-- Nume -->
                            <div class="mb-3">
                                <label class="form-label">Nume complet</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" name="nume_complet" class="form-control"
                                        value="<?php echo $row['nume_complet']; ?>" required>
                                </div>
                            </div>

                            <!-- Activ -->
                            <div class="mb-4">
                                <label class="form-label">Status</label>
                                <select name="activ" class="form-select">
                                    <option value="1" <?php if ($row['activ'] == 1)
                                        echo "selected"; ?>>Activ</option>
                                    <option value="0" <?php if ($row['activ'] == 0)
                                        echo "selected"; ?>>Inactiv</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-save me-2"></i>Salvează modificările
                            </button>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>