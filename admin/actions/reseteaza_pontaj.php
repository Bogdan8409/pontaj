<?php
// actions/reseteaza_pontaj.php
// primul comentariu in git
// 3444666655 comentariu in git


as
fd
asd as
d as
da
 dsa
 d 
 ass
 d as
  d
$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) die("Eroare DB: " . $mysqli->connect_error);

$pontaj_id = isset($_POST['pontaj_id']) ? (int)$_POST['pontaj_id'] : 0;
$redirect  = $_POST['redirect'] ?? '../pontaje.php';

if ($pontaj_id <= 0) {
    header("Location: {$redirect}?eroare=id_invalid");
    exit;
}

$stmt = $mysqli->prepare(
    "UPDATE pontaje
     SET status = 'Pending', motiv_respingere = NULL, data_procesare = NULL
     WHERE id = ? AND status = 'Respins'"
);
$stmt->bind_param("i", $pontaj_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: {$redirect}?succes=pontaj_resetat&id={$pontaj_id}");
} else {
    header("Location: {$redirect}?eroare=resetare_esuata&id={$pontaj_id}");
}
exit;
