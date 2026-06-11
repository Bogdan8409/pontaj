<?php
// actions/respinge_pontaj.php

$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) die("Eroare DB: " . $mysqli->connect_error);

$pontaj_id       = isset($_POST['pontaj_id'])       ? (int)$_POST['pontaj_id']              : 0;
$motiv_respingere = isset($_POST['motiv_respingere']) ? trim($_POST['motiv_respingere'])      : '';
$redirect        = $_POST['redirect']               ?? '../pontaje.php';

if ($pontaj_id <= 0) {
    header("Location: {$redirect}?eroare=id_invalid");
    exit;
}

if ($motiv_respingere === '') {
    header("Location: {$redirect}?eroare=motiv_lipsa&id={$pontaj_id}");
    exit;
}

$stmt = $mysqli->prepare(
    "UPDATE pontaje
     SET status = 'Respins', motiv_respingere = ?, data_procesare = NOW()
     WHERE id = ?"
);
$stmt->bind_param("si", $motiv_respingere, $pontaj_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: {$redirect}?succes=pontaj_respins&id={$pontaj_id}");
} else {
    header("Location: {$redirect}?eroare=respingere_esuata&id={$pontaj_id}");
}
exit;
