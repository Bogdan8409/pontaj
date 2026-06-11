<?php
require_once '../../db.php';
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

verificaAdminAutentificat(); // redirect /login dacă nu e logat

// ===== DATE DIN FORMULAR =====
$idPontaj = $_POST['id_pontaj'] ?? null;
$motivRespingere = trim($_POST['motiv_respingere'] ?? '');

if (!$idPontaj || $motivRespingere == '') {
    die('Date incomplete');
}

// ===== PRELUARE DATE PONTAJ =====
$sql = "
SELECT p.*, 
       u.nume AS nume_utilizator, u.email,
       c.nume AS nume_client
FROM pontaje p
JOIN utilizatori u ON u.id = p.user_id
JOIN clienti c ON c.id = p.client_id
WHERE p.id = ?
";
$stmt = $db->prepare($sql);
$stmt->execute([$idPontaj]);
$pontaj = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pontaj) {
    die('Pontaj inexistent');
}

// ===== UPDATE STATUS =====
$sql = "
UPDATE pontaje SET
    status = 'RESPINS',
    motiv_respingere = ?,
    respins_de = ?,
    data_respingere = NOW()
WHERE id = ?
";
$stmt = $db->prepare($sql);
$stmt->execute([
    $motivRespingere,
    $_SESSION['admin_id'],
    $idPontaj
]);

// ===== LINK FORMULAR PREPOPULAT =====
$linkFormular = BASE_URL . "/admin/pontaj/edit.php?id=" . $idPontaj;

// ===== EMAIL =====
$mail->addAddress($pontaj['email'], $pontaj['nume_utilizator']);
$mail->isHTML(true);

$mail->Subject = "Pontaj respins – Actiune necesara – " . $pontaj['luna'];

$mail->Body = "
<h2>Pontaj respins</h2>

<p>Buna ziua <b>{$pontaj['nume_utilizator']}</b>,</p>

<p>Pontajul dumneavoastra pentru luna <b>{$pontaj['luna']}</b> a fost <b>respins</b>.</p>

<p><b>Motiv respingere:</b></p>
<p>{$motivRespingere}</p>

<p><b>Detalii pontaj:</b></p>
<ul>
  <li>Luna: {$pontaj['luna']}</li>
  <li>Client: {$pontaj['nume_client']}</li>
  <li>Zile facturate: {$pontaj['zile_facturate']}</li>
  <li>Serie / Numar factura: {$pontaj['serie_factura']} {$pontaj['numar_factura']}</li>
  <li>Numar referinta: #{$pontaj['id']}</li>
</ul>

<p>
<a href='{$linkFormular}'>Corecteaza si retrimite pontajul</a>
</p>

<p>
Cu stima,<br>
<b>Sistem Gestionare Pontaje</b><br>
2WEB SOFTWARE SRL
</p>
";

$mail->AltBody = "
Pontaj respins

Luna: {$pontaj['luna']}
Client: {$pontaj['nume_client']}
Zile facturate: {$pontaj['zile_facturate']}
Serie / Numar factura: {$pontaj['serie_factura']} {$pontaj['numar_factura']}
Referinta: #{$pontaj['id']}

Motiv:
{$motivRespingere}

$linkFormular
";

$mail->send();

// ===== REDIRECT =====
header("Location: ../dashboard.php?msg=respins");
exit;
