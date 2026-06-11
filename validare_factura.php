<?php
// Exemplu de validare pe server
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numar_factura = $_POST['numar_factura'];
    
    // Validare pentru număr întreg pozitiv
    if (!is_numeric($numar_factura) || $numar_factura <= 0 || floor($numar_factura) != $numar_factura) {
        http_response_code(400);
        echo json_encode(['error' => 'Numărul facturii trebuie să fie un întreg pozitiv']);
        exit;
    }
    
    // Validare pentru număr unic (dacă e cazul)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM facturi WHERE numar_factura = ?");
    $stmt->execute([$numar_factura]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Numărul facturii există deja']);
        exit;
    }
    
    // Procesare continuă...
}





if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['timesheet_file'])) {
    $file = $_FILES['timesheet_file'];
    
    // Verificare erori upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Eroare la upload: ' . $file['error']);
    }
    
    // Verificare tip MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if ($mime !== 'application/pdf') {
        die('Fișierul trebuie să fie PDF');
    }
    
    // Verificare dimensiune (10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        die('Fișierul este prea mare (max 10MB)');
    }
    
    // Verificare nume fișier
    $filename = $file['name'];
    $pattern = '/^(0[1-9]|1[0-2])_(19|20)\d{2}\.pdf$/i';
    
    if (!preg_match($pattern, $filename)) {
        die('Denumire incorectă. Folosiți formatul: &lt;MM&gt;_&lt;YYYY&gt;.PDF');
    }
    
    // Extrage luna și anul din nume
    $parts = explode('_', $filename);
    $month = intval($parts[0]);
    $year = intval(substr($parts[1], 0, 4));
    
    // Verificare date valide
    if ($month < 1 || $month > 12) {
        die('Luna invalidă în denumirea fișierului');
    }
    
    if ($year < 2000 || $year > 2100) {
        die('An invalid în denumirea fișierului');
    }
    
    // Salvare fișier
    $uploadDir = 'uploads/timesheets/';
    $destination = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die('Eroare la salvarea fișierului');
    }
    
    echo 'Fișier încărcat cu succes!';
}

?>