<?php
include "send-mail.php";
$mysqli = new mysqli("localhost","root","","2web_pontaj");

if($mysqli->connect_errno){
    die("Eroare DB: " . $mysqli->connect_error);
}

if($_SERVER["REQUEST_METHOD"] === "POST"){

$luna_facturare = $_POST['billingMonth'];
$nume_prenume = $_POST['fullName'];
$zile_rezervate = $_POST['daysReserved'];
$zile_facturate = $_POST['zile_facturate'];
$client_id = $_POST['nume_client'];
$serie_factura = $_POST['serie_factura'];
$numar_factura = $_POST['numar_factura'];
$email = $_POST['email_colaborator'];

$status = "pending";
$data_trimitere = date("Y-m-d H:i:s");


sendEmail($email, $nume_prenume, $serie_factura, $numar_factura, $zile_facturate, $zile_rezervate);

/* ===============================
UPLOAD PDF
================================ */

$upload_dir = "uploads/timesheets/";

if(!is_dir($upload_dir)){
mkdir($upload_dir,0777,true);
}

$original_name = $_FILES['timesheet_file']['name'];
$tmp_name = $_FILES['timesheet_file']['tmp_name'];

$filename = time() . "_" . basename($original_name);
$path = $upload_dir . $filename;

move_uploaded_file($tmp_name,$path);
// move_uploaded_file($tmp_name,$filename);


/* ===============================
INSERT SQL
================================ */

$stmt = $mysqli->prepare("

INSERT INTO pontaje
(
luna_facturare,
nume_prenume,
zile_rezervate,
zile_facturate,
client_id,
serie_factura,
numar_factura,
timesheet_path,
timesheet_original_name,
status,
data_trimitere,
email_colaborator,
created_at
)

VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())

");

$stmt->bind_param(
"ssddisssssss",
$luna_facturare,
$nume_prenume,
$zile_rezervate,
$zile_facturate,
$client_id,
$serie_factura,
$numar_factura,
$filename,
$original_name,
$status,
$data_trimitere,
$email
);

$stmt->execute();

if($stmt->affected_rows > 0){

echo "Pontaj trimis cu succes!";

}else{

echo "Eroare salvare.";

}


header("Location: test.php?msg=ok");

// exit;
}