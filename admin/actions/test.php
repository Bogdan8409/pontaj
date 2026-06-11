<?php
require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php';

$pdf = new TCPDF();

$pdf->AddPage();

$pdf->Write(0, 'Test PDF TCPDF');

$pdf->Output('test.pdf', 'I');