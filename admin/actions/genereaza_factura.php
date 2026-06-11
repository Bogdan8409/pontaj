<?php
require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php';
require __DIR__ . '/../../vendor/autoload.php';

// 1. Conexiune DB
$mysqli = new mysqli("localhost", "root", "", "2web_pontaj");
if ($mysqli->connect_errno) {
    die("Eroare conexiune DB: " . $mysqli->connect_error);
}

// 2. Preluare si validare pontaj_id din POST
$pontaj_id = $_POST['pontaj_id'] ?? '';
if ($pontaj_id === '' || !is_numeric($pontaj_id)) {
    die("ID pontaj invalid");
}
$pontaj_id = (int) $pontaj_id;

// 3. Preluare pontaj din DB
$stmt = $mysqli->prepare("
    SELECT p.*
    FROM pontaje p
    WHERE p.id = ?
");
$stmt->bind_param("i", $pontaj_id);
$stmt->execute();
$pontaj = $stmt->get_result()->fetch_assoc();

if (!$pontaj) {
    die("Pontaj inexistent");
}

// 4. Creare folder facturi daca nu exista
$folder = __DIR__ . '/../../facturi/';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// 5. Definire nume fisier PDF
$serie_sanitized = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pontaj['serie_factura']);
$numar_sanitized = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pontaj['numar_factura']);
$pdf_filename = "Factura_{$serie_sanitized}_{$numar_sanitized}_{$pontaj_id}.pdf";
$pdf_path = $folder . $pdf_filename;
$pdf_url = "facturi/" . $pdf_filename; // path relativ salvat in DB

// 6. Generare PDF cu TCPDF
$pdf = new TCPDF();
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

// ===== TITLU =====
$pdf->SetFont('dejavusans', 'B', 18);
$pdf->Cell(0, 10, 'FACTURA', 0, 1, 'L');

$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(0, 6, "Numar {$pontaj['numar_factura']}", 0, 1);

$pdf->Cell(
    0,
    6,
    "Data " . date('d.m.Y') .
    "    Scadent la " . date('d.m.Y'),
    0,
    1
);

$pdf->Ln(5);

// ===== FURNIZOR =====
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->Cell(0, 6, '2WEB SOFTWARE SRL', 0, 1);

$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 6, 'CIF RO12345678', 0, 1);
$pdf->Cell(0, 6, 'Bucuresti, Romania', 0, 1);

$pdf->Ln(5);

// Linie separator
$pdf->Cell(0, 0, '', 'T', 1);
$pdf->Ln(3);

// ===== TABEL =====
$pdf->SetFont('dejavusans', 'B', 10);

$pdf->Cell(15, 7, 'Nr.', 0, 0);
$pdf->Cell(75, 7, 'Denumire servicii', 0, 0);
$pdf->Cell(30, 7, 'Zile facturate', 0, 0, 'C');
$pdf->Cell(35, 7, 'Nr total de zile', 0, 0, 'C');
$pdf->Cell(35, 7, 'Valoare', 0, 1, 'R');

$pdf->Cell(0, 0, '', 'T', 1);

$pdf->SetFont('dejavusans', '', 10);

$zile_facturate = (int) $pontaj['zile_facturate'];
$nr_total_zile = (int) $pontaj['nr_total_zile'];

$pret_pe_zi = 100;

$valoare = $pret_pe_zi * $zile_facturate;

$pdf->Cell(15, 7, '1', 0, 0);

$pdf->Cell(
    75,
    7,
    'Servicii prestate conform contract',
    0,
    0
);

$pdf->Cell(
    30,
    7,
    $zile_facturate,
    0,
    0,
    'C'
);

$pdf->Cell(
    35,
    7,
    $nr_total_zile,
    0,
    0,
    'C'
);

$pdf->Cell(
    35,
    7,
    number_format($valoare, 2) . ' RON',
    0,
    1,
    'R'
);

$pdf->Cell(0, 0, '', 'T', 1);

// ===== TOTAL =====
$pdf->Ln(5);

$pdf->SetFont('dejavusans', 'B', 11);

$pdf->Cell(140, 7, 'Total:', 0, 0, 'R');

$pdf->Cell(
    50,
    7,
    number_format($valoare, 2) . ' RON',
    0,
    1,
    'R'
);
// --- Semnatura
$pdf->Ln(15);
$pdf->SetFont('dejavusans', '', 9);
$pdf->Cell(95, 6, "Emis de: 2WEB SOFTWARE SRL", 0, 0, 'C');
$pdf->Cell(95, 6, "Semnatura: ___________________", 0, 0, 'C');

// 7. Salvare PDF pe disc
$pdf->Output($pdf_path, 'F');

// 8. Verificare ca fisierul a fost creat
if (!file_exists($pdf_path)) {
    die("Eroare: PDF-ul nu a putut fi salvat pe disc.");
}

// 9. Actualizare DB – salveaza path-ul si seteaza status Aprobat
$update_stmt = $mysqli->prepare(
    "UPDATE pontaje SET factura_pdf_path = ?, data_procesare = NOW() WHERE id = ?"
);
$update_stmt->bind_param("si", $pdf_url, $pontaj_id);
if (!$update_stmt->execute()) {
    die("Eroare actualizare DB: " . $update_stmt->error);
}

// 10. Redirect inapoi cu mesaj succes
$redirect = trim($_POST['redirect'] ?? '../pontaje.php');
if ($redirect === '') {
    $redirect = '../pontaje.php';
}

// Dacă formularul trimite doar un nume de fișier din admin, îl prefăcem într-un redirect relativ către folderul părinte.
if (!preg_match('#^(?:https?://|/|\.\./)#', $redirect)) {
    $redirect = '../' . ltrim($redirect, '/');
}

header("Location: {$redirect}?succes=factura_generata&id={$pontaj_id}");
exit;
