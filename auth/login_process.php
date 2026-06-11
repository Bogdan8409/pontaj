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