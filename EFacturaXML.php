<?php
declare(strict_types=1);

/**
 * ============================================================
 * EFACTURA XML - RO e-Factura / UBL 2.1
 * ============================================================
 *
 * Baza de date:
 *   2web_pontaj
 *
 * Tabele folosite:
 *   pontaje  (sursa principala a datelor facturii)
 *   clienti
 *   setari   (date furnizor, randul cu cheie = 'date_firma')
 *
 * XML-ul se genereaza direct din randul din 'pontaje',
 * la fel ca PDF-ul generat de actions/genereaza_factura.php.
 * Nu se mai foloseste un tabel separat 'facturi'.
 *
 * Nu necesita config/database.php
 *
 * Utilizare (parametrul 'id' este ID-ul din tabela 'pontaje'):
 *
 *   http://localhost/pontaj/EFacturaXML.php?id=1
 *
 * ============================================================
 */


/* ============================================================
   1. CONFIGURARE MYSQL
   ============================================================ */

$dbHost = '127.0.0.1';
$dbName = '2web_pontaj';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';


/* ============================================================
   2. DIRECTOR XML
   ============================================================ */

$xmlDirectory = __DIR__
    . DIRECTORY_SEPARATOR . 'storage'
    . DIRECTORY_SEPARATOR . 'facturi'
    . DIRECTORY_SEPARATOR . 'xml';


/* ============================================================
   3. HEADERE
   ============================================================ */

header('Content-Type: text/html; charset=UTF-8');


/* ============================================================
   4. CONECTARE DATABASE
   ============================================================ */

try {

    $dsn =
        "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
                false
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die(
        '<h2>Eroare conectare MySQL</h2>' .
        '<pre>' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        ) .
        '</pre>'
    );
}


/* ============================================================
   5. FUNCȚII
   ============================================================ */

function xmlText(?string $value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}


function money(float $value): string
{
    return number_format(
        $value,
        2,
        '.',
        ''
    );
}


function decimalValue(float $value): string
{
    return rtrim(
        rtrim(
            number_format(
                $value,
                4,
                '.',
                ''
            ),
            '0'
        ),
        '.'
    );
}


function normalizeCui(string $cui): string
{
    $cui = strtoupper(trim($cui));

    $cui = preg_replace(
        '/[^A-Z0-9]/',
        '',
        $cui
    );

    return (string)$cui;
}


function cuiNumeric(string $cui): string
{
    $cui = normalizeCui($cui);

    if (str_starts_with($cui, 'RO')) {
        return substr($cui, 2);
    }

    return $cui;
}


/* ============================================================
   VALIDARE CIFRA DE CONTROL CUI (algoritm oficial ANAF)
   ============================================================ */

function cuiChecksumValid(string $cui): bool
{
    $numeric = cuiNumeric($cui);

    if (
        $numeric === '' ||
        !ctype_digit($numeric)
    ) {
        return false;
    }

    $len = strlen($numeric);

    /*
     * CUI-urile RO au între 2 și 10 cifre
     * (ultima fiind cifra de control).
     */

    if ($len < 2 || $len > 10) {
        return false;
    }

    $controlDigit =
        (int)substr($numeric, -1);

    $base =
        str_pad(
            substr($numeric, 0, $len - 1),
            9,
            '0',
            STR_PAD_LEFT
        );

    $key = [7, 5, 3, 2, 1, 7, 5, 3, 2];

    $sum = 0;

    for ($i = 0; $i < 9; $i++) {

        $sum +=
            (int)$base[$i] * $key[$i];
    }

    $rest = ($sum * 10) % 11;

    if ($rest === 10) {
        $rest = 0;
    }

    return $rest === $controlDigit;
}


/* ============================================================
   JUDET -> COD ISO 3166-2:RO
   ============================================================ */

function judetIso(string $judet): string
{
    $judet = trim($judet);

    if ($judet === '') {
        return '';
    }

    /*
     * Dacă vine deja în formatul corect (ex: "RO-VN"),
     * îl păstrăm neschimbat.
     */

    if (preg_match('/^RO-[A-Z]{1,2}$/', strtoupper($judet))) {
        return strtoupper($judet);
    }

    $normalize = static function (string $value): string {

        $value = strtolower(trim($value));

        $value = strtr(
            $value,
            [
                'ă' => 'a', 'â' => 'a', 'î' => 'i',
                'ș' => 's', 'ş' => 's',
                'ț' => 't', 'ţ' => 't'
            ]
        );

        return $value;
    };

    $map = [
        'alba' => 'RO-AB',
        'arad' => 'RO-AR',
        'arges' => 'RO-AG',
        'bacau' => 'RO-BC',
        'bihor' => 'RO-BH',
        'bistrita-nasaud' => 'RO-BN',
        'bistrita nasaud' => 'RO-BN',
        'botosani' => 'RO-BT',
        'brasov' => 'RO-BV',
        'braila' => 'RO-BR',
        'buzau' => 'RO-BZ',
        'caras-severin' => 'RO-CS',
        'caras severin' => 'RO-CS',
        'calarasi' => 'RO-CL',
        'cluj' => 'RO-CJ',
        'constanta' => 'RO-CT',
        'covasna' => 'RO-CV',
        'dambovita' => 'RO-DB',
        'dolj' => 'RO-DJ',
        'galati' => 'RO-GL',
        'giurgiu' => 'RO-GR',
        'gorj' => 'RO-GJ',
        'harghita' => 'RO-HR',
        'hunedoara' => 'RO-HD',
        'ialomita' => 'RO-IL',
        'iasi' => 'RO-IS',
        'ilfov' => 'RO-IF',
        'maramures' => 'RO-MM',
        'mehedinti' => 'RO-MH',
        'mures' => 'RO-MS',
        'neamt' => 'RO-NT',
        'olt' => 'RO-OT',
        'prahova' => 'RO-PH',
        'satu mare' => 'RO-SM',
        'salaj' => 'RO-SJ',
        'sibiu' => 'RO-SB',
        'suceava' => 'RO-SV',
        'teleorman' => 'RO-TR',
        'timis' => 'RO-TM',
        'tulcea' => 'RO-TL',
        'vaslui' => 'RO-VS',
        'valcea' => 'RO-VL',
        'vrancea' => 'RO-VN',
        'bucuresti' => 'RO-B',
        'municipiul bucuresti' => 'RO-B',
        'bucuresti sector 1' => 'RO-B',
        'bucuresti sector 2' => 'RO-B',
        'bucuresti sector 3' => 'RO-B',
        'bucuresti sector 4' => 'RO-B',
        'bucuresti sector 5' => 'RO-B',
        'bucuresti sector 6' => 'RO-B'
    ];

    $key = $normalize($judet);

    return $map[$key] ?? '';
}


/* ============================================================
   ORAS -> JUDET (fallback cand lipseste coloana 'judet')
   ============================================================ */

function judetIsoFromCity(string $city): string
{
    $city = trim($city);

    if ($city === '') {
        return '';
    }

    $normalize = static function (string $value): string {

        $value = strtolower(trim($value));

        $value = strtr(
            $value,
            [
                'ă' => 'a', 'â' => 'a', 'î' => 'i',
                'ș' => 's', 'ş' => 's',
                'ț' => 't', 'ţ' => 't'
            ]
        );

        return $value;
    };

    /*
     * Acoperă reședințele de județ și câteva orașe mari
     * frecvent întâlnite. Nu e o listă exhaustivă a tuturor
     * localităților din România — pentru acuratețe completă,
     * completează coloana 'judet' din tabela clienti.
     */

    $map = [
        'alba iulia' => 'RO-AB',
        'arad' => 'RO-AR',
        'pitesti' => 'RO-AG',
        'bacau' => 'RO-BC',
        'oradea' => 'RO-BH',
        'bistrita' => 'RO-BN',
        'botosani' => 'RO-BT',
        'brasov' => 'RO-BV',
        'braila' => 'RO-BR',
        'buzau' => 'RO-BZ',
        'resita' => 'RO-CS',
        'calarasi' => 'RO-CL',
        'cluj-napoca' => 'RO-CJ',
        'cluj napoca' => 'RO-CJ',
        'constanta' => 'RO-CT',
        'sfantu gheorghe' => 'RO-CV',
        'targoviste' => 'RO-DB',
        'craiova' => 'RO-DJ',
        'galati' => 'RO-GL',
        'giurgiu' => 'RO-GR',
        'targu jiu' => 'RO-GJ',
        'miercurea ciuc' => 'RO-HR',
        'deva' => 'RO-HD',
        'slobozia' => 'RO-IL',
        'iasi' => 'RO-IS',
        'baia mare' => 'RO-MM',
        'drobeta-turnu severin' => 'RO-MH',
        'drobeta turnu severin' => 'RO-MH',
        'targu mures' => 'RO-MS',
        'piatra neamt' => 'RO-NT',
        'slatina' => 'RO-OT',
        'ploiesti' => 'RO-PH',
        'satu mare' => 'RO-SM',
        'zalau' => 'RO-SJ',
        'sibiu' => 'RO-SB',
        'suceava' => 'RO-SV',
        'alexandria' => 'RO-TR',
        'timisoara' => 'RO-TM',
        'tulcea' => 'RO-TL',
        'vaslui' => 'RO-VS',
        'ramnicu valcea' => 'RO-VL',
        'focsani' => 'RO-VN',
        'bucuresti' => 'RO-B'
    ];

    $key = $normalize($city);

    return $map[$key] ?? '';
}


/* ============================================================
   SETARE DIN DATABASE
   ============================================================ */

function getSetting(
    PDO $pdo,
    string $key,
    bool $required = false
): string {

    $stmt = $pdo->prepare(
        "
        SELECT valoare
        FROM setari
        WHERE cheie = :cheie
        LIMIT 1
        "
    );

    $stmt->execute([
        ':cheie' => $key
    ]);

    $value = $stmt->fetchColumn();

    if ($value === false) {
        $value = '';
    }

    $value = trim((string)$value);

    if (
        $required &&
        $value === ''
    ) {

        throw new RuntimeException(
            'Lipsește setarea: ' . $key
        );
    }

    return $value;
}


/* ============================================================
   SETARI FURNIZOR (rand dedicat, coloane separate)
   ============================================================ */

function getFurnizorSettings(
    PDO $pdo,
    string $key = 'date_firma'
): array {

    $stmt = $pdo->prepare(
        "
        SELECT
            furnizor_nume,
            furnizor_cui,
            furnizor_reg_com,
            furnizor_adresa,
            furnizor_localitate,
            furnizor_judet,
            furnizor_cod_postal,
            furnizor_telefon,
            furnizor_email,
            furnizor_banca,
            furnizor_iban
        FROM setari
        WHERE cheie = :cheie
        LIMIT 1
        "
    );

    $stmt->execute([
        ':cheie' => $key
    ]);

    $row = $stmt->fetch();

    if (!$row) {

        throw new RuntimeException(
            'Lipsesc setările furnizorului (cheie: ' . $key . ').'
        );
    }

    return $row;
}


/* ============================================================
   XML BASIC ELEMENT
   ============================================================ */

function addCBC(
    DOMDocument $xml,
    DOMElement $parent,
    string $name,
    string $value = ''
): DOMElement {

    $node = $xml->createElementNS(
        'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
        'cbc:' . $name
    );

    if ($value !== '') {

        $node->appendChild(
            $xml->createTextNode($value)
        );
    }

    $parent->appendChild($node);

    return $node;
}


/* ============================================================
   XML CAC ELEMENT
   ============================================================ */

function addCAC(
    DOMDocument $xml,
    DOMElement $parent,
    string $name
): DOMElement {

    $node = $xml->createElementNS(
        'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
        'cac:' . $name
    );

    $parent->appendChild($node);

    return $node;
}


/* ============================================================
   XML ATTRIBUTE
   ============================================================ */

function addAttribute(
    DOMElement $node,
    string $name,
    string $value
): void {

    $node->setAttribute(
        $name,
        $value
    );
}


/* ============================================================
   6. PONTAJ ID
   ============================================================ */

$pontajId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$pontajId) {

    http_response_code(400);

    die(
        '<h2>ID pontaj lipsă</h2>' .
        '<p>Exemplu:</p>' .
        '<pre>EFacturaXML.php?id=1</pre>'
    );
}


/* ============================================================
   7. CITIRE PONTAJ
   ============================================================ */

$stmt = $pdo->prepare(
    "
    SELECT *
    FROM pontaje
    WHERE id = :id
    LIMIT 1
    "
);

$stmt->execute([
    ':id' => $pontajId
]);

$pontaj = $stmt->fetch();

if (!$pontaj) {

    http_response_code(404);

    die(
        '<h2>Pontajul nu exista</h2>'
    );
}

if (
    strtolower(trim((string)$pontaj['status']))
    !== 'aprobat'
) {

    http_response_code(400);

    die(
        '<h2>Pontajul nu este aprobat</h2>' .
        '<p>XML-ul de e-Factura poate fi generat ' .
        'doar pentru pontaje cu status "aprobat".</p>'
    );
}

if (
    trim((string)$pontaj['serie_factura']) === '' ||
    trim((string)$pontaj['numar_factura']) === ''
) {

    throw new RuntimeException(
        'Pontajul nu are serie/număr de factură completate.'
    );
}


/* ============================================================
   8. CITIRE CLIENT
   ============================================================ */

$stmt = $pdo->prepare(
    "
    SELECT *
    FROM clienti
    WHERE id = :id
    LIMIT 1
    "
);

$stmt->execute([
    ':id' => $pontaj['client_id']
]);

$client = $stmt->fetch();

if (!$client) {

    throw new RuntimeException(
        'Clientul pontajului nu exista.'
    );
}


/* ============================================================
   9. FURNIZOR
   ============================================================ */

$furnizorRow = getFurnizorSettings($pdo);

$furnizor = [

    'nume' =>
        trim((string)($furnizorRow['furnizor_nume'] ?? '')),

    'cui' =>
        trim((string)($furnizorRow['furnizor_cui'] ?? '')),

    'reg_com' =>
        trim((string)($furnizorRow['furnizor_reg_com'] ?? '')),

    'adresa' =>
        trim((string)($furnizorRow['furnizor_adresa'] ?? '')),

    'localitate' =>
        trim((string)($furnizorRow['furnizor_localitate'] ?? '')),

    'judet' =>
        trim((string)($furnizorRow['furnizor_judet'] ?? '')),

    'cod_postal' =>
        trim((string)($furnizorRow['furnizor_cod_postal'] ?? '')),

    'telefon' =>
        trim((string)($furnizorRow['furnizor_telefon'] ?? '')),

    'email' =>
        trim((string)($furnizorRow['furnizor_email'] ?? '')),

    'banca' =>
        trim((string)($furnizorRow['furnizor_banca'] ?? '')),

    'iban' =>
        trim((string)($furnizorRow['furnizor_iban'] ?? ''))
];


/*
 * Câmpuri obligatorii (păstrează aceeași validare
 * ca înainte, când se foloseau setări separate).
 */

foreach (['nume', 'cui', 'adresa', 'localitate'] as $requiredField) {

    if ($furnizor[$requiredField] === '') {

        throw new RuntimeException(
            'Lipsește câmpul furnizor_' . $requiredField .
            ' din tabela setari (rândul cu cheie = date_firma).'
        );
    }
}


/* ============================================================
   10. DATE FACTURA (calculate direct din pontaj)
   ============================================================ */

/*
 * Preț fix pe zi facturată, RON.
 *
 * ATENȚIE: aceeași valoare este folosită și în
 * actions/genereaza_factura.php (generarea PDF-ului).
 * Dacă se schimbă aici, trebuie schimbată și acolo,
 * altfel PDF-ul și XML-ul vor avea sume diferite.
 */

$pretPeZi = 100;

$serie = trim(
    (string)$pontaj['serie_factura']
);

$numar = trim(
    (string)$pontaj['numar_factura']
);

$invoiceId =
    $serie . $numar;

$dataEmitere = date('Y-m-d');

$dataScadenta = date('Y-m-d');

$moneda = 'RON';

$zileFacturate =
    (float)(
        $pontaj['zile_facturate']
        ?? 0
    );

$subtotal =
    $pretPeZi * $zileFacturate;

/*
 * Nu există calcul de TVA în fluxul actual (vezi și
 * actions/genereaza_factura.php) — se presupune
 * furnizor neplătitor de TVA / scutit.
 */

$tva = 0.0;

$total = $subtotal;


/* ============================================================
   11. TVA
   ============================================================ */

$tvaPercent = 0;

if ($subtotal > 0 && $tva > 0) {

    $tvaPercent =
        round(
            ($tva / $subtotal) * 100,
            2
        );
}


/* ============================================================
   12. CLIENT
   ============================================================ */

$clientNume =
    trim(
        (string)$client['nume']
    );

$clientCui =
    trim(
        (string)$client['cui']
    );

$clientCuiNumeric =
    cuiNumeric($clientCui);

if (
    $clientCuiNumeric !== '' &&
    !cuiChecksumValid($clientCui)
) {

    throw new RuntimeException(
        'CUI-ul clientului "' .
        $clientNume .
        '" (' .
        $clientCui .
        ') nu este valid (cifra de control nu ' .
        'corespunde). Verifică CUI-ul în tabela clienti.'
    );
}

/*
 * BR-CO-09: cbc:CompanyID trebuie să înceapă cu prefixul
 * de țară ISO ("RO"), indiferent cum a fost introdus CUI-ul
 * clientului în baza de date.
 */

$clientCuiNormalized =
    'RO' . $clientCuiNumeric;

$clientAdresa =
    trim(
        (string)$client['adresa']
    );

$clientTelefon =
    trim(
        (string)$client['telefon']
    );

$clientEmail =
    trim(
        (string)$client['email']
    );


/*
 * Tabela clienti are (de regulă) o singură coloană pentru adresă.
 *
 * BR-RO-092 cere obligatoriu orașul cumpărătorului (BT-52).
 * Dacă în viitor se adaugă o coloană dedicată (ex: 'localitate'
 * sau 'oras') în tabela clienti, aceasta are prioritate.
 * Ca fallback, extragem orașul din ultimul segment al adresei
 * (text de după ultima virgulă), acolo unde adresa e scrisă
 * in formatul "Strada nr. X, Oras".
 */

$clientLocalitate =
    trim(
        (string)(
            $client['localitate']
            ?? $client['oras']
            ?? ''
        )
    );

if ($clientLocalitate === '' && $clientAdresa !== '') {

    $adresaParts =
        explode(',', $clientAdresa);

    if (count($adresaParts) > 1) {

        $clientLocalitate =
            trim(
                (string)end($adresaParts)
            );
    }
}

if ($clientLocalitate === '') {

    throw new RuntimeException(
        'Localitatea clientului (BT-52) lipsește. ' .
        'Adaugă localitatea în tabela clienti sau ' .
        'scrie adresa în formatul "Strada, Oraș".'
    );
}

/*
 * Județul clientului (BT-54), necesar pentru BR-RO-111
 * ca subdiviziune ISO 3166-2:RO.
 *
 * Ordine de căutare:
 *   1. Coloana dedicată 'judet' din tabela clienti (dacă există).
 *   2. Deducere din orașul deja extras ($clientLocalitate),
 *      pentru reședințele de județ cunoscute.
 *   3. Dacă tot nu se poate determina, oprim generarea și
 *      cerem completarea manuală — o presupunere greșită
 *      a județului ar produce o factură invalidă la SPV.
 */

$clientJudetRaw =
    trim(
        (string)(
            $client['judet']
            ?? ''
        )
    );

$clientJudet =
    judetIso(
        $clientJudetRaw
    );

if ($clientJudet === '') {

    $clientJudet =
        judetIsoFromCity(
            $clientLocalitate
        );
}

if ($clientJudet === '') {

    throw new RuntimeException(
        'Județul clientului "' .
        $clientNume .
        '" (BT-54) lipsește sau nu este recunoscut. ' .
        'Adaugă o coloană "judet" în tabela clienti ' .
        'cu numele județului (ex: "Cluj") sau codul ISO ' .
        '(ex: "RO-CJ"), sau completează orașul cu o ' .
        'reședință de județ recunoscută (ex: "Cluj-Napoca").'
    );
}

$clientCodPostal = '';


/* ============================================================
   13. DATE PONTAJ (colaborator / lună facturare)
   ============================================================ */

$numeColaborator =
    trim(
        (string)(
            $pontaj['nume_prenume']
            ?? ''
        )
    );

$lunaFacturare =
    trim(
        (string)(
            $pontaj['luna_facturare']
            ?? ''
        )
    );


/* ============================================================
   15. CANTITATE
   ============================================================ */

$cantitate =
    $zileFacturate;

if ($cantitate <= 0) {

    $cantitate = 1;
}


/* ============================================================
   16. PREȚ UNITAR
   ============================================================ */

$pretUnitar =
    $subtotal / $cantitate;


/* ============================================================
   17. DESCRIERE
   ============================================================ */

$descriere =
    'Servicii conform pontaj';


if ($lunaFacturare !== '') {

    $timestamp =
        strtotime(
            $lunaFacturare
        );

    if ($timestamp !== false) {

        $descriere .=
            ' - ' .
            date(
                'm/Y',
                $timestamp
            );
    }
}


if ($numeColaborator !== '') {

    $descriere .=
        ' - ' .
        $numeColaborator;
}


/* ============================================================
   18. CUI FURNIZOR
   ============================================================ */

$furnizorCuiNumeric =
    cuiNumeric(
        $furnizor['cui']
    );

if (
    $furnizorCuiNumeric !== '' &&
    !cuiChecksumValid($furnizor['cui'])
) {

    throw new RuntimeException(
        'CUI-ul furnizorului (' .
        $furnizor['cui'] .
        ') nu este valid (cifra de control nu ' .
        'corespunde). Verifică setarea ' .
        'efactura_furnizor_cui.'
    );
}

/*
 * BR-CO-09: cbc:CompanyID din PartyTaxScheme trebuie să
 * înceapă obligatoriu cu prefixul de țară ISO (ex: "RO").
 * Reconstruim mereu cu prefixul RO, indiferent cum a fost
 * introdus în setare (cu sau fără "RO").
 */

$furnizorCui =
    'RO' . $furnizorCuiNumeric;


/* ============================================================
   19. CREARE DOCUMENT XML
   ============================================================ */

$xml =
    new DOMDocument(
        '1.0',
        'UTF-8'
    );

$xml->formatOutput = true;


/* ============================================================
   20. ROOT UBL INVOICE
   ============================================================ */

$invoice =
    $xml->createElementNS(
        'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
        'Invoice'
    );


/*
 * Namespace-uri UBL
 */

$invoice->setAttributeNS(
    'http://www.w3.org/2000/xmlns/',
    'xmlns:cac',
    'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
);

$invoice->setAttributeNS(
    'http://www.w3.org/2000/xmlns/',
    'xmlns:cbc',
    'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
);


$xml->appendChild(
    $invoice
);


/* ============================================================
   21. CUSTOMIZATION ID
   ============================================================ */

addCBC(
    $xml,
    $invoice,
    'CustomizationID',
    'urn:cen.eu:en16931:2017#compliant#urn:efactura.mfinante.ro:CIUS-RO:1.0.1'
);


/* ============================================================
   22. ID FACTURA
   ============================================================ */

addCBC(
    $xml,
    $invoice,
    'ID',
    $invoiceId
);


/* ============================================================
   23. DATA EMITERII
   ============================================================ */

addCBC(
    $xml,
    $invoice,
    'IssueDate',
    $dataEmitere
);


/* ============================================================
   24. DATA SCADENTA
   ============================================================ */

addCBC(
    $xml,
    $invoice,
    'DueDate',
    $dataScadenta
);


/* ============================================================
   25. TIP FACTURA
   ============================================================ */

addCBC(
    $xml,
    $invoice,
    'InvoiceTypeCode',
    '380'
);


/* ============================================================
   26. MONEDA
   ============================================================ */

addCBC(
    $xml,
    $invoice,
    'DocumentCurrencyCode',
    $moneda
);

/* ============================================================
   27. FURNIZOR
   ============================================================ */

$supplierParty =
    addCAC(
        $xml,
        $invoice,
        'AccountingSupplierParty'
    );

$supplier =
    addCAC(
        $xml,
        $supplierParty,
        'Party'
    );


/*
 * Identificare furnizor
 *
 * Pentru CUI folosim schemeID 9925.
 */

if ($furnizorCuiNumeric !== '') {

    $endpoint =
        addCBC(
            $xml,
            $supplier,
            'EndpointID',
            $furnizorCuiNumeric
        );

    addAttribute(
        $endpoint,
        'schemeID',
        '9925'
    );


    $partyIdentification =
        addCAC(
            $xml,
            $supplier,
            'PartyIdentification'
        );

    addCBC(
        $xml,
        $partyIdentification,
        'ID',
        $furnizorCuiNumeric
    );
}


/* ============================================================
   NUME FURNIZOR
   ============================================================ */

$partyName =
    addCAC(
        $xml,
        $supplier,
        'PartyName'
    );

addCBC(
    $xml,
    $partyName,
    'Name',
    $furnizor['nume']
);


/* ============================================================
   ADRESA FURNIZOR
   ============================================================ */

$supplierAddress =
    addCAC(
        $xml,
        $supplier,
        'PostalAddress'
    );


addCBC(
    $xml,
    $supplierAddress,
    'StreetName',
    $furnizor['adresa']
);


if (
    $furnizor['localitate'] !== ''
) {

    addCBC(
        $xml,
        $supplierAddress,
        'CityName',
        $furnizor['localitate']
    );
}


if (
    $furnizor['cod_postal'] !== ''
) {

    addCBC(
        $xml,
        $supplierAddress,
        'PostalZone',
        $furnizor['cod_postal']
    );
}


$furnizorJudetIso =
    judetIso(
        $furnizor['judet']
    );

if (
    $furnizor['judet'] !== '' &&
    $furnizorJudetIso === ''
) {

    throw new RuntimeException(
        'Județul furnizorului ("' .
        $furnizor['judet'] .
        '") nu a putut fi asociat unui cod ISO 3166-2:RO. ' .
        'Verifică setarea efactura_furnizor_judet.'
    );
}

if ($furnizorJudetIso === '') {

    throw new RuntimeException(
        'Județul furnizorului (BT-39) lipsește. ' .
        'Completează setarea efactura_furnizor_judet ' .
        '(ex: "Vrancea" sau direct "RO-VN").'
    );
}

addCBC(
    $xml,
    $supplierAddress,
    'CountrySubentity',
    $furnizorJudetIso
);


/* ============================================================
   TARA FURNIZOR
   ============================================================ */

$country =
    addCAC(
        $xml,
        $supplierAddress,
        'Country'
    );

addCBC(
    $xml,
    $country,
    'IdentificationCode',
    'RO'
);


/* ============================================================
   TAX SCHEME FURNIZOR
   ============================================================ */

$taxSchemeParty =
    addCAC(
        $xml,
        $supplier,
        'PartyTaxScheme'
    );

addCBC(
    $xml,
    $taxSchemeParty,
    'CompanyID',
    $furnizorCui
);


$taxScheme =
    addCAC(
        $xml,
        $taxSchemeParty,
        'TaxScheme'
    );

addCBC(
    $xml,
    $taxScheme,
    'ID',
    'VAT'
);


/* ============================================================
   ENTITATE JURIDICA FURNIZOR
   ============================================================ */

$legalEntity =
    addCAC(
        $xml,
        $supplier,
        'PartyLegalEntity'
    );

addCBC(
    $xml,
    $legalEntity,
    'RegistrationName',
    $furnizor['nume']
);


if (
    $furnizor['reg_com'] !== ''
) {

    addCBC(
        $xml,
        $legalEntity,
        'CompanyID',
        $furnizor['reg_com']
    );
}


/* ============================================================
   CONTACT FURNIZOR
   ============================================================ */

if (
    $furnizor['telefon'] !== '' ||
    $furnizor['email'] !== ''
) {

    $contact =
        addCAC(
            $xml,
            $supplier,
            'Contact'
        );


    if (
        $furnizor['telefon'] !== ''
    ) {

        addCBC(
            $xml,
            $contact,
            'Telephone',
            $furnizor['telefon']
        );
    }


    if (
        $furnizor['email'] !== ''
    ) {

        addCBC(
            $xml,
            $contact,
            'ElectronicMail',
            $furnizor['email']
        );
    }
}


/* ============================================================
   28. CLIENT
   ============================================================ */

$customerParty =
    addCAC(
        $xml,
        $invoice,
        'AccountingCustomerParty'
    );

$customer =
    addCAC(
        $xml,
        $customerParty,
        'Party'
    );


/* ============================================================
   IDENTIFICARE CLIENT
   ============================================================ */

if (
    $clientCuiNumeric !== ''
) {

    $endpoint =
        addCBC(
            $xml,
            $customer,
            'EndpointID',
            $clientCuiNumeric
        );

    addAttribute(
        $endpoint,
        'schemeID',
        '9925'
    );


    $partyIdentification =
        addCAC(
            $xml,
            $customer,
            'PartyIdentification'
        );

    addCBC(
        $xml,
        $partyIdentification,
        'ID',
        $clientCuiNumeric
    );
}


/* ============================================================
   NUME CLIENT
   ============================================================ */

$customerName =
    addCAC(
        $xml,
        $customer,
        'PartyName'
    );

addCBC(
    $xml,
    $customerName,
    'Name',
    $clientNume
);


/* ============================================================
   ADRESA CLIENT
   ============================================================ */

$customerAddress =
    addCAC(
        $xml,
        $customer,
        'PostalAddress'
    );


if ($clientAdresa !== '') {

    addCBC(
        $xml,
        $customerAddress,
        'StreetName',
        $clientAdresa
    );
}


if ($clientLocalitate !== '') {

    addCBC(
        $xml,
        $customerAddress,
        'CityName',
        $clientLocalitate
    );
}


if ($clientCodPostal !== '') {

    addCBC(
        $xml,
        $customerAddress,
        'PostalZone',
        $clientCodPostal
    );
}


if ($clientJudet !== '') {

    addCBC(
        $xml,
        $customerAddress,
        'CountrySubentity',
        $clientJudet
    );
}


/* ============================================================
   TARA CLIENT
   ============================================================ */

$customerCountry =
    addCAC(
        $xml,
        $customerAddress,
        'Country'
    );

addCBC(
    $xml,
    $customerCountry,
    'IdentificationCode',
    'RO'
);


/* ============================================================
   TAX SCHEME CLIENT
   ============================================================ */

if (
    $clientCuiNumeric !== ''
) {

    $customerTax =
        addCAC(
            $xml,
            $customer,
            'PartyTaxScheme'
        );

    addCBC(
        $xml,
        $customerTax,
        'CompanyID',
        $clientCuiNormalized
    );


    $customerTaxScheme =
        addCAC(
            $xml,
            $customerTax,
            'TaxScheme'
        );

    addCBC(
        $xml,
        $customerTaxScheme,
        'ID',
        'VAT'
    );
}


/* ============================================================
   ENTITATE JURIDICA CLIENT
   ============================================================ */

$customerLegal =
    addCAC(
        $xml,
        $customer,
        'PartyLegalEntity'
    );

addCBC(
    $xml,
    $customerLegal,
    'RegistrationName',
    $clientNume
);


/* ============================================================
   CONTACT CLIENT
   ============================================================ */

if (
    $clientTelefon !== '' ||
    $clientEmail !== ''
) {

    $customerContact =
        addCAC(
            $xml,
            $customer,
            'Contact'
        );


    if (
        $clientTelefon !== ''
    ) {

        addCBC(
            $xml,
            $customerContact,
            'Telephone',
            $clientTelefon
        );
    }


    if (
        $clientEmail !== ''
    ) {

        addCBC(
            $xml,
            $customerContact,
            'ElectronicMail',
            $clientEmail
        );
    }
}


/* ============================================================
   29. PAYMENT MEANS
   ============================================================ */

$paymentMeans =
    addCAC(
        $xml,
        $invoice,
        'PaymentMeans'
    );


$paymentCode =
    addCBC(
        $xml,
        $paymentMeans,
        'PaymentMeansCode',
        '30'
    );


/* ============================================================
   IBAN
   ============================================================ */

if (
    $furnizor['iban'] !== ''
) {

    $account =
        addCAC(
            $xml,
            $paymentMeans,
            'PayeeFinancialAccount'
        );


    addCBC(
        $xml,
        $account,
        'ID',
        $furnizor['iban']
    );


    /*
     * CIUS-RO (UBL-CR-430, UBL-CR-664) nu permite elementul
     * cac:FinancialInstitution în PayeeFinancialAccount.
     * Numele băncii nu se transmite în e-Factura RO;
     * IBAN-ul de mai sus este suficient.
     */
}


/* ============================================================
   30. TAX TOTAL
   ============================================================ */

$taxTotal =
    addCAC(
        $xml,
        $invoice,
        'TaxTotal'
    );


$taxAmount =
    addCBC(
        $xml,
        $taxTotal,
        'TaxAmount',
        money($tva)
    );

addAttribute(
    $taxAmount,
    'currencyID',
    $moneda
);


/* ============================================================
   TAX SUBTOTAL
   ============================================================ */

$taxSubtotal =
    addCAC(
        $xml,
        $taxTotal,
        'TaxSubtotal'
    );


$taxableAmount =
    addCBC(
        $xml,
        $taxSubtotal,
        'TaxableAmount',
        money($subtotal)
    );

addAttribute(
    $taxableAmount,
    'currencyID',
    $moneda
);


$taxSubAmount =
    addCBC(
        $xml,
        $taxSubtotal,
        'TaxAmount',
        money($tva)
    );

addAttribute(
    $taxSubAmount,
    'currencyID',
    $moneda
);


/* ============================================================
   TAX CATEGORY
   ============================================================ */

$taxCategory =
    addCAC(
        $xml,
        $taxSubtotal,
        'TaxCategory'
    );


/*
 * S = TVA standard
 * Z = zero rated
 */

$taxCategoryCode =
    ($tva > 0)
        ? 'S'
        : 'Z';


addCBC(
    $xml,
    $taxCategory,
    'ID',
    $taxCategoryCode
);


addCBC(
    $xml,
    $taxCategory,
    'Percent',
    money($tvaPercent)
);


$categoryScheme =
    addCAC(
        $xml,
        $taxCategory,
        'TaxScheme'
    );

addCBC(
    $xml,
    $categoryScheme,
    'ID',
    'VAT'
);


/* ============================================================
   31. LEGAL MONETARY TOTAL
   ============================================================ */

$monetaryTotal =
    addCAC(
        $xml,
        $invoice,
        'LegalMonetaryTotal'
    );


$lineExtensionAmount =
    addCBC(
        $xml,
        $monetaryTotal,
        'LineExtensionAmount',
        money($subtotal)
    );

addAttribute(
    $lineExtensionAmount,
    'currencyID',
    $moneda
);


$taxExclusiveAmount =
    addCBC(
        $xml,
        $monetaryTotal,
        'TaxExclusiveAmount',
        money($subtotal)
    );

addAttribute(
    $taxExclusiveAmount,
    'currencyID',
    $moneda
);


$taxInclusiveAmount =
    addCBC(
        $xml,
        $monetaryTotal,
        'TaxInclusiveAmount',
        money($total)
    );

addAttribute(
    $taxInclusiveAmount,
    'currencyID',
    $moneda
);


$payableAmount =
    addCBC(
        $xml,
        $monetaryTotal,
        'PayableAmount',
        money($total)
    );

addAttribute(
    $payableAmount,
    'currencyID',
    $moneda
);


/* ============================================================
   32. INVOICE LINE
   ============================================================ */

$invoiceLine =
    addCAC(
        $xml,
        $invoice,
        'InvoiceLine'
    );


addCBC(
    $xml,
    $invoiceLine,
    'ID',
    '1'
);


/* ============================================================
   CANTITATE
   ============================================================ */

$quantity =
    addCBC(
        $xml,
        $invoiceLine,
        'InvoicedQuantity',
        decimalValue($cantitate)
    );

addAttribute(
    $quantity,
    'unitCode',
    'DAY'
);


/* ============================================================
   VALOARE LINIE
   ============================================================ */

$lineAmount =
    addCBC(
        $xml,
        $invoiceLine,
        'LineExtensionAmount',
        money($subtotal)
    );

addAttribute(
    $lineAmount,
    'currencyID',
    $moneda
);


/* ============================================================
   ITEM
   ============================================================ */

$item =
    addCAC(
        $xml,
        $invoiceLine,
        'Item'
    );


addCBC(
    $xml,
    $item,
    'Name',
    $descriere
);


/* ============================================================
   TAX CATEGORY ITEM
   ============================================================ */

$itemTaxCategory =
    addCAC(
        $xml,
        $item,
        'ClassifiedTaxCategory'
    );


addCBC(
    $xml,
    $itemTaxCategory,
    'ID',
    $taxCategoryCode
);


addCBC(
    $xml,
    $itemTaxCategory,
    'Percent',
    money($tvaPercent)
);


$itemTaxScheme =
    addCAC(
        $xml,
        $itemTaxCategory,
        'TaxScheme'
    );


addCBC(
    $xml,
    $itemTaxScheme,
    'ID',
    'VAT'
);


/* ============================================================
   PRICE
   ============================================================ */

$price =
    addCAC(
        $xml,
        $invoiceLine,
        'Price'
    );


$priceAmount =
    addCBC(
        $xml,
        $price,
        'PriceAmount',
        money($pretUnitar)
    );


addAttribute(
    $priceAmount,
    'currencyID',
    $moneda
);


/* ============================================================
   33. SALVARE XML STRING
   ============================================================ */

$xmlContent =
    $xml->saveXML();

if ($xmlContent === false) {

    throw new RuntimeException(
        'Nu s-a putut genera continutul XML.'
    );
}


/* ============================================================
   34. VALIDARE XML SINTACTIC
   ============================================================ */

libxml_use_internal_errors(true);

$testXml =
    simplexml_load_string(
        $xmlContent
    );

if ($testXml === false) {

    $errors =
        libxml_get_errors();

    $errorText = '';

    foreach ($errors as $error) {

        $errorText .=
            trim(
                $error->message
            ) .
            ' - linia ' .
            $error->line .
            "\n";
    }

    libxml_clear_errors();

    throw new RuntimeException(
        "XML invalid:\n" .
        $errorText
    );
}

libxml_clear_errors();


/* ============================================================
   35. DIRECTOR
   ============================================================ */

if (!is_dir($xmlDirectory)) {

    if (
        !mkdir(
            $xmlDirectory,
            0775,
            true
        )
    ) {

        throw new RuntimeException(
            'Nu s-a putut crea directorul: ' .
            $xmlDirectory
        );
    }
}


/* ============================================================
   36. NUME FISIER
   ============================================================ */

$safeSerie =
    preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $serie
    );

$safeNumar =
    preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $numar
    );


$fileName =
    'Factura_' .
    $safeSerie .
    '_' .
    $safeNumar .
    '.xml';


$filePath =
    $xmlDirectory .
    DIRECTORY_SEPARATOR .
    $fileName;


/* ============================================================
   37. SALVARE
   ============================================================ */

$result =
    file_put_contents(
        $filePath,
        $xmlContent,
        LOCK_EX
    );


if ($result === false) {

    throw new RuntimeException(
        'Nu s-a putut salva fisierul XML.'
    );
}


/* ============================================================
   38. PATH RELATIV
   ============================================================ */

$relativePath =
    'storage/facturi/xml/' .
    $fileName;


/* ============================================================
   39. UPDATE PONTAJ (status XML)
   ============================================================ */

$stmt =
    $pdo->prepare(
        "
        UPDATE pontaje
        SET
            xml_path = :xml_path,
            xml_status = :xml_status,
            xml_generated_at = NOW()
        WHERE id = :id
        "
    );


$stmt->execute([
    ':xml_path' =>
        $relativePath,

    ':xml_status' =>
        'generat',

    ':id' =>
        $pontajId
]);


/* ============================================================
   40. AFISARE
   ============================================================ */

?>
<!DOCTYPE html>

<html lang="ro">

<head>

<meta charset="UTF-8">

<title>
XML e-Factura <?= htmlspecialchars($invoiceId) ?>
</title>

<style>

body {
    margin: 0;
    padding: 40px;
    background: #f3f4f6;
    font-family: Arial, sans-serif;
}

.container {
    max-width: 1100px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 20px rgba(0,0,0,.08);
}

h1 {
    margin-top: 0;
}

.success {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    line-height: 1.8;
}

.buttons {
    margin-top: 20px;
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 12px 18px;
    margin-right: 8px;
    border-radius: 6px;
    text-decoration: none;
    background: #0d6efd;
    color: white;
}

.btn:hover {
    opacity: .9;
}

pre {
    background: #111827;
    color: #e5e7eb;
    padding: 20px;
    border-radius: 8px;
    overflow: auto;
    white-space: pre;
    font-size: 13px;
    line-height: 1.5;
}

</style>

</head>

<body>

<div class="container">

<h1>
✓ XML e-Factura generat
</h1>

<div class="success">

XML-ul a fost generat cu succes și este XML well-formed.

</div>

<div class="info">

<strong>Factura:</strong>
<?= htmlspecialchars($invoiceId) ?>

<br>

<strong>Client:</strong>
<?= htmlspecialchars($clientNume) ?>

<br>

<strong>Data emitere:</strong>
<?= htmlspecialchars($dataEmitere) ?>

<br>

<strong>Subtotal:</strong>
<?= htmlspecialchars(money($subtotal)) ?>
<?= htmlspecialchars($moneda) ?>

<br>

<strong>TVA:</strong>
<?= htmlspecialchars(money($tva)) ?>
<?= htmlspecialchars($moneda) ?>

<br>

<strong>Total:</strong>
<?= htmlspecialchars(money($total)) ?>
<?= htmlspecialchars($moneda) ?>

<br>

<strong>Pontaj:</strong>
<?= $pontaj ? 'Găsit' : 'Nu a fost găsit' ?>

<br>

<strong>Fișier:</strong>
<?= htmlspecialchars($fileName) ?>

<br>

<strong>Status:</strong>
generat

</div>


<div class="buttons">

<a
    class="btn"
    href="<?= htmlspecialchars($relativePath) ?>"
    target="_blank"
>
Vezi XML
</a>


<a
    class="btn"
    href="<?= htmlspecialchars($relativePath) ?>"
    download
>
Descarcă XML
</a>

</div>


<h2>
XML
</h2>

<pre><?= htmlspecialchars($xmlContent) ?></pre>

</div>

</body>

</html>