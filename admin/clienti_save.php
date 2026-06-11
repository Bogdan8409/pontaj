<?php
require '../includes/db.php';

if (!empty($_POST['id'])) {
    $stmt = $pdo->prepare("
        UPDATE clienti SET nume=?, adresa=?, info_factura=?
        WHERE id=?
    ");
    $stmt->execute([
        $_POST['nume'],
        $_POST['adresa'],
        $_POST['info'],
        $_POST['id']
    ]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO clienti (nume, adresa, info_factura)
        VALUES (?,?,?)
    ");
    $stmt->execute([
        $_POST['nume'],
        $_POST['adresa'],
        $_POST['info']
    ]);
}

echo json_encode(['success'=>true]);
