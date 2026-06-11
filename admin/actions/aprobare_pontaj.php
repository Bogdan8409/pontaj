<?php
// actions/aprobare_pontaj.php

$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) die("Eroare DB: " . $mysqli->connect_error);

$pontaj_id = isset($_POST['pontaj_id']) ? (int)$_POST['pontaj_id'] : 0;
$redirect  = '../pontaje.php';

if ($pontaj_id <= 0) {
    header("Location: {$redirect}?eroare=id_invalid");
    exit;
}

$stmt = $mysqli->prepare(
    "UPDATE pontaje SET status = 'Aprobat', data_procesare = NOW() WHERE id = ?"
);
$stmt->bind_param("i", $pontaj_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: {$redirect}?succes=pontaj_aprobat&id={$pontaj_id}");
    // header("Location: ../pontaje.php?succes=pontaj_aprobat&id={$pontaj_id}");
} else {
    header("Location: {$redirect}?eroare=aprobare_esuata&id={$pontaj_id}");
}
exit;
