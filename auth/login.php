<?php
session_start();

// require '../includes/db.php';
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

if ($conn->connect_error) {
    die("Conexiune esuata: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // caută utilizatorul după email
    $sql = "SELECT id, username, email, password_hash, activ 
            FROM admini 
            WHERE email = ? 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // verifică dacă este activ
        if ($user['activ'] != 1) {
            die("Cont inactiv.");
        }

        // verifică parola
        if (password_verify($password, $user['password_hash'])) {

            // salvare sesiune
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // update ultima autentificare
            $update = $conn->prepare("
                UPDATE admini 
                SET ultima_autentificare = NOW() 
                WHERE id = ?
            ");

            $update->bind_param("i", $user['id']);
            $update->execute();

            header("Location: ../admin/index.php");
            exit;

        } else {
            echo "Parolă greșită.";
        }

    } else {
        echo "Email inexistent.";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AdminLTE Login</title>

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- AdminLTE -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">

<div class="login-box">

    <!-- Logo -->
    <div class="login-logo">
        <a href="#"><b>Admin</b>LTE</a>
    </div>

    <!-- Card -->
    <div class="card">
        <div class="card-body login-card-body">

            <p class="login-box-msg">
                Sign in to start your session
            </p>

            <form action="" method="post">

                <!-- Email -->
                <div class="input-group mb-3">
                    <input type="email"
                           class="form-control"
                           placeholder="Email"
                           id="email"
                           name="email"
                           required>

                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="input-group mb-3">
                    <input type="password"
                           class="form-control"
                           placeholder="Password"
                           id="password"
                           name="password"
                           required>

                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <!-- Button -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit"
                                class="btn btn-primary btn-block">
                            Login
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

</div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>