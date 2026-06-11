<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acces invalid.");
}

if (!isset($_FILES['fileUpload']) || $_FILES['fileUpload']['error'] !== 0) {
    die("Fișier lipsă.");
}

// Verificare MIME real
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['fileUpload']['tmp_name']);

if ($mime !== 'application/pdf') {
    die("Doar PDF este permis.");
}

// Folder upload (asigură-te că există și are permisiuni)
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 🔥 NUME NOU: MM_YYYY.pdf
$newFileName = date("m_Y") . ".pdf";
$destination = $uploadDir . $newFileName;

// Mutăm fișierul
if (!move_uploaded_file($_FILES['fileUpload']['tmp_name'], $destination)) {
    die("Eroare la salvarea fișierului.");
}

echo "Fișier încărcat cu succes ca: <strong>$newFileName</strong>";

  require __DIR__ . '/vendor/autoload.php';

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  $mail = new PHPMailer(true);

  $f = fopen($_FILES['incarcare_timesheet']['tmp_name'], 'rb');
  $header = fread($f, 4);
  fclose($f);

  if ($header !== "%PDF") {
    die("Fișierul nu este un PDF valid.");
  }


  $subject = "[PONTAJ NOU] $numeColaborator - $luna";
  $mail->isHTML(true);
$mail->Subject = $subject;

$mail->Body = "
<h2>Pontaj nou trimis</h2>
<p><b>Colaborator:</b> $numeColaborator</p>
<p><b>Luna:</b> $luna</p>
<p><b>Client:</b> $numeClient</p>
<p><b>Zile rezervate:</b> $zileRezervate</p>
<p><b>Zile facturate:</b> $zileFacturate</p>
<p><b>Factura:</b> $serie $numar</p>
<p><b>Data trimiterii:</b> ".date('d.m.Y H:i')."</p>

<p>
<a href='$linkAdmin'>Revizuire pontaj</a>
</p>
";
$mail->AltBody = "
Pontaj nou trimis

Colaborator: $numeColaborator
Luna: $luna
Client: $numeClient
Zile rezervate: $zileRezervate
Zile facturate: $zileFacturate
Factura: $serie $numar
Data: ".date('d.m.Y H:i')."

$linkAdmin
";
$mail->addAttachment(
  $_FILES['incarcare_timesheet']['tmp_name'],
  $_FILES['incarcare_timesheet']['name']
);

try {
    // Setări server SMTP
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = '54a761d8030e06';
    $mail->Password   = '4ec87321ef4207';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 465;
    // Destinatar
    $mail->setFrom('noreply@pontaj.com', 'Sistem Pontaj');
    $mail->addAddress('bcurteanu@yahoo.com');
    $mail->send();
} catch (Exception $e) {
    echo "Eroare la trimiterea emailului: {$mail->ErrorInfo}";
    exit;
}


?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Date formular</title>
</head>
<body>

<h2>Datele trimise din formular</h2>




<?php
echo "<pre>";
print_r($_POST);
echo "</pre>";
?>


</body>
</html>
