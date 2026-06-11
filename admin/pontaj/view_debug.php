<?php
session_start();

// ── TEMPORARY DEBUG — remove before production ───────────────────────────────
$debug = true;   // set false to disable

function dbg(string $label, mixed $val): void {
    global $debug;
    if ($debug) {
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:8px;margin:4px 0;font-size:13px;">';
        echo '<b style="color:#9cdcfe">' . htmlspecialchars($label) . '</b>: ';
        echo htmlspecialchars(print_r($val, true));
        echo '</pre>';
    }
}

// ── Validare ID ──────────────────────────────────────────────────────────────
$idPontaj = $_GET['id'] ?? null;
dbg('$_GET[id] raw', $idPontaj);
dbg('ctype_digit check', ctype_digit((string)$idPontaj));

if (!$idPontaj || !ctype_digit((string)$idPontaj)) {
    die('Pontaj invalid');
}
$idPontaj = (int)$idPontaj;
dbg('$idPontaj (int)', $idPontaj);

// ── Conexiune BD ─────────────────────────────────────────────────────────────
$mysqli = new mysqli('localhost', 'root', '', '2web_pontaj');
dbg('connect_errno', $mysqli->connect_errno);
dbg('connect_error', $mysqli->connect_error);

if ($mysqli->connect_errno) {
    die('Eroare conexiune BD: ' . $mysqli->connect_error);
}

// ── Verifică dacă tabelul există și ce coloane are ───────────────────────────
$tables = [];
$r = $mysqli->query("SHOW TABLES");
while ($row = $r->fetch_row()) { $tables[] = $row[0]; }
dbg('Tables in DB', $tables);

$cols = [];
$r2 = $mysqli->query("SHOW COLUMNS FROM pontaje");
if ($r2) { while ($row = $r2->fetch_assoc()) { $cols[] = $row['Field']; } }
dbg('Columns in pontaje', $cols);

// ── Încearcă SELECT simplu fără JOIN ─────────────────────────────────────────
$raw = $mysqli->query("SELECT id, status, nume_prenume FROM pontaje WHERE id = $idPontaj");
dbg('Simple SELECT result', $raw ? $raw->fetch_assoc() : 'QUERY FAILED: ' . $mysqli->error);

// ── SELECT cu JOIN (query original) ──────────────────────────────────────────
$stmt = $mysqli->prepare(
    'SELECT p.id, p.luna_facturare, p.nume_prenume, p.zile_rezervate,
            p.zile_facturate, p.nr_total_zile, p.client_id,
            p.serie_factura, p.numar_factura,
            p.timesheet_path, p.timesheet_original_name,
            p.status, p.motiv_respingere,
            p.data_trimitere, p.data_procesare,
            p.factura_pdf_path, p.email_colaborator,
            p.created_at, p.updated_at,
            c.nume AS client_nume
     FROM   pontaje p
     LEFT JOIN clienti c ON p.client_id = c.id
     WHERE  p.id = ?'
);

if (!$stmt) {
    dbg('prepare() error', $mysqli->error);
    die('Prepare failed');
}

$stmt->bind_param('i', $idPontaj);
$stmt->execute();
dbg('execute() error', $stmt->error);

$result = $stmt->get_result();
$pontaj = $result->fetch_assoc();
$stmt->close();

dbg('$pontaj', $pontaj);

if (!$pontaj) {
    // Show all IDs that exist so you can verify
    $allIds = [];
    $r3 = $mysqli->query("SELECT id FROM pontaje ORDER BY id LIMIT 20");
    if ($r3) { while ($row = $r3->fetch_row()) { $allIds[] = $row[0]; } }
    dbg('Existing pontaj IDs (first 20)', $allIds);

    $mysqli->close();
    die('<b style="color:red">Pontaj inexistent</b> — see debug info above');
}

$mysqli->close();
echo '<div style="background:#d4edda;padding:10px;font-family:sans-serif">
  <b>✅ Pontaj găsit cu succes!</b><br>
  ID: ' . htmlspecialchars($pontaj['id']) . ' — ' . htmlspecialchars($pontaj['nume_prenume']) . '
</div>';
dbg('Full $pontaj row', $pontaj);