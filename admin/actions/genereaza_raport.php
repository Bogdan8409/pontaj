<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once('../../conexiune.php');
require_once('../../vendor/autoload.php');

// 🔒 Validare POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['pontaj_id'])) {
    die('Acces invalid');
}

$pontaj_id = (int)$_POST['pontaj_id'];

// 1️⃣ Pontaj
$stmt = $mysqli->prepare("SELECT * FROM pontaje WHERE id = ?");
$stmt->bind_param("i", $pontaj_id);
$stmt->execute();
$pontaj = $stmt->get_result()->fetch_assoc();

if (!$pontaj) {
    die('Pontaj inexistent');
}

// 2️⃣ Client
$stmt = $mysqli->prepare("SELECT * FROM clienti WHERE id = ?");
$stmt->bind_param("i", $pontaj['client']);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

if (!$client) {
    die('Client inexistent');
}

// 3️⃣ Calcule
$pret_zi = 200;
$subtotal = $pontaj['zile_facturate'] * $pret_zi;
$tva = $subtotal * 0.19;
$total = $subtotal + $tva;

// 4️⃣ PDF
$pdf = new TCPDF();
$pdf->AddPage();

$html = "
<h2>2WEB SOFTWARE SRL</h2>
<hr>
<p>
<strong>Factura:</strong> {$pontaj['factura_serie']}-{$pontaj['factura_numar']}<br>
<strong>Data:</strong> ".date('d.m.Y')."
</p>

<p>
<strong>Client:</strong> {$client['nume']}<br>
CUI: {$client['CUI']}<br>
Adresa: {$client['Adresa']}
</p>

<p>
<strong>Colaborator:</strong> {$pontaj['colaborator']}
</p>

<table border='1' cellpadding='5'>
<tr>
<th>Serviciu</th>
<th>Zile</th>
<th>Pret/zi</th>
<th>Total</th>
</tr>
<tr>
<td>Servicii IT</td>
<td>{$pontaj['zile_facturate']}</td>
<td>{$pret_zi} RON</td>
<td>{$subtotal} RON</td>
</tr>
</table>

<p>
Subtotal: {$subtotal} RON<br>
TVA 19%: {$tva} RON<br>
<strong>Total de plată: {$total} RON</strong>
</p>
";

$pdf->writeHTML($html);

// 5️⃣ Salvare
$filename = "Factura_{$pontaj['factura_serie']}_{$pontaj['factura_numar']}.pdf";
$path = "../../facturi/$filename";
$pdf->Output($path, 'F');

// 6️⃣ Redirect înapoi cu link
header("Location: ../rapoarte_pontaje.php?factura=$filename");
exit;
