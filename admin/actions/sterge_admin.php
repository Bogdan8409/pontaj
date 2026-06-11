<?php
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

if ($conn->connect_error) {
    die("Eroare conexiune: " . $conn->connect_error);
}

/* ======================
   VALIDARE ID
====================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalid");
}

$id = (int) $_GET['id'];

/* ======================
   OPTIONAL: NU permite ștergere admin principal
====================== */
// if ($id == 1) {
//     die("Nu poți șterge adminul principal!");
// }

/* ======================
   ȘTERGERE SECURIZATĂ
====================== */
$stmt = $conn->prepare("DELETE FROM admini WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../setari.php?msg=sters");
    exit();
} else {
    echo "Eroare la ștergere!";
}

$stmt->close();
$conn->close();
?>