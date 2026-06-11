<?php
require '../includes/db.php';

$parola = password_hash($_POST['password'], PASSWORD_BCRYPT);

if (!empty($_POST['id'])) {
    $stmt = $pdo->prepare("
        UPDATE admini SET nume=?, email=?, rol=? WHERE id=?
    ");
    $stmt->execute([
        $_POST['nume'],
        $_POST['email'],
        $_POST['rol'],
        $_POST['id']
    ]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO admini (nume, email, parola, rol)
        VALUES (?,?,?,?)
    ");
    $stmt->execute([
        $_POST['nume'],
        $_POST['email'],
        $parola,
        $_POST['rol']
    ]);
}

echo json_encode(['success'=>true]);
