<?php
require_once('../../vendor/autoload.php'); // TCPDF

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Simulare date pontaj + client + colaborator
$pontaj = [
    'serie' => 'F',
    'numar' => '001',
    'data_emitere' => date('d-m-Y'),
    'data_scadenta' => date('d-m-Y', strtotime('+30 days')),
    'colaborator' => 'Ion Popescu',
    'client_nume' => 'SC Client SRL',
    'client_detalii' => [
        'CUI' => 'RO12345678',
        'RegCom' => 'J40/1234/2020',
        'Adresa' => 'Str. Exemplu 10, Bucuresti',
        'Telefon' => '0123456789',
        'Email' => 'contact@client.ro',
        'Banca' => 'Banca Exemplar',
        'IBAN' => 'RO49AAAA1B31007593840000'
    ],
    'servicii' => [
        [
            'denumire' => 'Servicii IT',
            'um' => 'Zile',
            'cantitate' => 20,
            'pret_unitar' => 200,
        ],
    ],
    'timesheet' => 'TS1234',
    'termeni' => 'Plata se efectueaza in termen de 30 zile de la emitere.'
];

// Calcul valori
$subtot = 0;
foreach ($pontaj['servicii'] as $srv) {
    $subtot += $srv['cantitate'] * $srv['pret_unitar'];
}
$tva = $subtot * 0.19; // exemplu TVA 19%
$total = $subtot + $tva;

// Nume fisier PDF
$filename = "Factura_{$pontaj['serie']}_{$pontaj['numar']}_{$pontaj['colaborator']}.pdf";
$filepath = __DIR__ . '/../../facturi/' . $filename;


// Generare PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('2WEB SOFTWARE SRL');
$pdf->SetAuthor('2WEB SOFTWARE SRL');
$pdf->SetTitle("Factura {$pontaj['serie']}-{$pontaj['numar']}");
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Logo
$pdf->Image('logo.png', 15, 10, 50);

// Detalii emitent
$html = '
<h2>2WEB SOFTWARE SRL</h2>
<p>
CUI: RO12345678<br>
Reg. Com.: J40/5678/2020<br>
Adresa: Str. Exemplu 1, Bucuresti<br>
Telefon: 021-1234567, Email: office@2web.ro<br>
Banca: Banca Exemplu, IBAN: RO49AAAA1B31007593840000
</p>
<hr>
';

// Serie si date factura
$html .= "
<p>
<strong>Factura Serie:</strong> {$pontaj['serie']}-{$pontaj['numar']}<br>
<strong>Data emiterii:</strong> {$pontaj['data_emitere']}<br>
<strong>Data scadentei:</strong> {$pontaj['data_scadenta']}
</p>
";

// Client si colaborator
$html .= "
<p>
<strong>Client:</strong> {$pontaj['client_nume']}<br>
<strong>Detalii Client:</strong><br>
CUI: {$pontaj['client_detalii']['CUI']}<br>
Reg. Com.: {$pontaj['client_detalii']['RegCom']}<br>
Adresa: {$pontaj['client_detalii']['Adresa']}<br>
Telefon: {$pontaj['client_detalii']['Telefon']}<br>
Email: {$pontaj['client_detalii']['Email']}<br>
Banca: {$pontaj['client_detalii']['Banca']}, IBAN: {$pontaj['client_detalii']['IBAN']}<br>
<strong>Colaborator:</strong> {$pontaj['colaborator']}
</p>
<hr>
";

// Tabel servicii
$html .= '<table border="1" cellpadding="5">
<tr style="background-color:#f2f2f2;">
<th>Nr.crt</th>
<th>Denumire serviciu</th>
<th>U.M.</th>
<th>Cantitate</th>
<th>Pret unitar</th>
<th>Valoare</th>
</tr>';

$nr = 1;
foreach ($pontaj['servicii'] as $srv) {
    $valoare = $srv['cantitate'] * $srv['pret_unitar'];
    $html .= "
    <tr>
        <td>$nr</td>
        <td>{$srv['denumire']}</td>
        <td>{$srv['um']}</td>
        <td>{$srv['cantitate']}</td>
        <td>{$srv['pret_unitar']} RON</td>
        <td>$valoare RON</td>
    </tr>";
    $nr++;
}

$html .= "</table><br>";

// Calcule
$html .= "
<p>
Subtotal: $subtot RON<br>
TVA (19%): $tva RON<br>
<strong>Total de plata: $total RON</strong>
</p>
";

// Timesheet + semnatura + termeni
$html .= "
<p>Numar timesheet atasat: {$pontaj['timesheet']}</p>
<p><img src='semnatura.png' width='100'> <br>Semnatura si stampila</p>
<p><strong>Termeni si conditii:</strong> {$pontaj['termeni']}</p>
";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output($filepath, 'F');

// Optional: trimite email cu PDF atasat folosind PHPMailer (la fel ca in exemplul anterior)
echo "Factura PDF generata: $filename";
header("Location: ../index.php?success=1");
