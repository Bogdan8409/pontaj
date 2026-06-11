<?php
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

if (
    !isset($_POST['id'], $_POST['action']) ||
    !ctype_digit($_POST['id'])
) {
    die('ID invalid');
}

$id = (int) $_POST['id'];
$action = $_POST['action'];

$status = match ($action) {
    'approve' => 'Aprobat',
    'reject'  => 'Respins',
    default   => die('Acțiune invalidă')
};

$stmt = $conn->prepare("UPDATE pontaje SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

header("Location: /admin/pontaj/pontaj.php?id=$id");
exit;
