<?php
require '../includes/db.php';

$pdo->prepare("DELETE FROM clienti WHERE id=?")
    ->execute([$_POST['id']]);

echo json_encode(['success'=>true]);
