<?php
require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php';

// Conectare DB
$conn = new mysqli("localhost", "root", "", "2web_pontaj");
if ($conn->connect_error) {
    die("DB error");
}

// Preluare pontaj_id din POST (buton din tabel)
$pontaj_id = $_POST['pontaj_id'] ?? '';
if (!ctype_digit((string)$pontaj_id)) {
    die("ID invalid");
}

// ======================
// LINII PONTAJ + CLIENT
// ======================
$stmt = $conn->prepare("
    SELECT p.*, c.nume AS nume_client, c.CUI, c.RegCom, c.Adresa, c.Telefon, c.Email, c.Banca, c.IBAN
    FROM pontaje p
    JOIN clienti c ON p.client_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $pontaj_id);
$stmt->execute();
$linii = $stmt->get_result();

if ($linii->num_rows === 0) {
    die("Pontaj inexistent");
}

$pontaj = $linii->fetch_assoc();

// ======================
// PDF
// ======================
$pdf = new TCPDF();
$pdf->SetCreator('2WEB SOFTWARE');
$pdf->SetAuthor('2WEB SOFTWARE SRL');
$pdf->SetTitle('Factura ' . $pontaj['serie_factura'] . '-' . $pontaj['numar_factura']);

$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

// LOGO
$pdf->Image(__DIR__ . '/../assets/logo.png', 15, 10, 40);

// INFO FIRMA
$pdf->SetXY(60, 10);
$pdf->MultiCell(
    120,
    5,
    "2WEB SOFTWARE SRL
CUI: RO12345678
Reg. Com: J40/1234/2020
Adresa sediu
Tel: 07xxxxxxx
Email: office@2web.ro
Banca XYZ
IBAN: RO00XXXX
",
    0
);

// FACTURA INFO
$pdf->Ln(35);
$pdf->SetFont('', 'B', 12);
$pdf->Cell(0, 10, "FACTURA", 0, 1, 'C');

$pdf->SetFont('', '', 10);
$pdf->Cell(100, 6, "Serie / Nr: {$pontaj['serie_factura']} {$pontaj['numar_factura']}", 0);
$pdf->Cell(0, 6, "Data: " . date('Y-m-d'), 0, 1); // Data generare

$pdf->Cell(100, 6, "Client: {$pontaj['nume_client']}", 0);
$pdf->Cell(0, 6, "Scadenta: " . date('Y-m-d', strtotime('+30 days')), 0, 1);

$pdf->Cell(100, 6, "Colaborator: {$pontaj['nume_prenume']}", 0, 1);

// TABEL SERVICII
$pdf->Ln(5);
$pdf->SetFont('', 'B', 9);
$pdf->Cell(10, 7, 'Nr', 1);
$pdf->Cell(60, 7, 'Serviciu', 1);
$pdf->Cell(15, 7, 'U.M.', 1);
$pdf->Cell(20, 7, 'Cant.', 1);
$pdf->Cell(25, 7, 'Pret', 1);
$pdf->Cell(30, 7, 'Valoare', 1);
$pdf->Ln();

$pdf->SetFont('', '', 9);

// Exemplu linie factura (poți înlocui cu date reale)
$pdf->Cell(10, 7, 1, 1);
$pdf->Cell(60, 7, 'Servicii IT', 1);
$pdf->Cell(15, 7, 'Zile', 1);
$pdf->Cell(20, 7, $pontaj['zile_facturate'], 1, 0, 'R');
$pdf->Cell(25, 7, '100', 1, 0, 'R');
$pdf->Cell(30, 7, $pontaj['zile_facturate']*100, 1, 1, 'R');

// TOTAL
$pdf->Ln(5);
$pdf->Cell(130);
$pdf->Cell(30, 6, 'Total:', 0);
$pdf->Cell(30, 6, $pontaj['zile_facturate']*100, 0, 1, 'R');

// OUTPUT
$pdf->Output(
    __DIR__ . "/../facturi/Factura_{$pontaj['serie_factura']}_{$pontaj['numar_factura']}.pdf",
    'FI'
);
