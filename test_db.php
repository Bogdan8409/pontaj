<?php

require_once __DIR__ . '/db.php';

echo '<h2>Conexiunea MySQL funcționează!</h2>';

echo '<p>Baza de date: 2web_pontaj</p>';

$stmt = $pdo->query(
    "SELECT COUNT(*) AS total FROM facturi"
);

$row = $stmt->fetch();

echo '<p>Număr facturi: ' .
     (int)$row['total'] .
     '</p>';