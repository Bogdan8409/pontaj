<?php
declare(strict_types=1);

/**
 * EFacturaXML.php
 *
 * Generator XML RO e-Factura UBL 2.1
 *
 * Compatibil cu:
 *   - facturi
 *   - clienti
 *   - pontaje
 *   - setari
 *
 * Nu necesita config/database.php.
 *
 * Utilizare:
 *   EFacturaXML.php?id=1
 *
 * Exemplu:
 *   http://localhost/pontaj/EFacturaXML.php?id=1
 */

// ============================================================
// CONFIGURARE BAZA DE DATE
// ============================================================

$dbHost = '127.0.0.1';
$dbName = '2web_pontaj';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

// ============================================================
// DIRECTOR XML
// ============================================================

$xmlDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'storage' .
                DIRECTORY_SEPARATOR . 'facturi' .
                DIRECTORY_SEPARATOR . 'xml';

// ============================================================
// HEADERE
// ============================================================

header('Content-Type: text/html; charset=UTF-8');

// ============================================================
// CONECTARE PDO
// ============================================================

try {

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die(
        '<h2>Eroare conexiune baza de date</h2>' .
        '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
    );
}

// ============================================================
// FUNCȚII
// ============================================================

function h(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}


/**
 * Citește o setare din tabela setari.
 */
function getSetting(PDO $pdo, string $key, bool $required = false): string
{
    $stmt = $pdo->prepare("
        SELECT valoare
        FROM setari
        WHERE cheie = :cheie
        LIMIT 1
    ");

    $stmt->execute([
        ':cheie' => $key
    ]);

    $value = $stmt->fetchColumn();

    $value = $value === false ? '' : trim((string)$value);

    if ($required && $value === '') {
        throw new RuntimeException(
            "Lipsește setarea furnizorului: {$key}"
        );
    }

    return $value;
}


/**
 * Normalizează CUI.
 */
function normalizeCui(string $cui): string
{
    $cui = strtoupper(trim($cui));

    $cui = preg_replace('/[^A-Z0-9]/', '', $cui);

    return (string)$cui;
}


/**
 * Returnează CUI fără RO.
 */
function cuiWithoutRo(string $cui): string
{
    $cui = normalizeCui($cui);

    if (str_starts_with($cui, 'RO')) {
        return substr($cui, 2);
    }

    return $cui;
}


/**
 * Formatează numerele pentru XML.
 */
function money(float $value): string
{
    return number_format(
        $value,
        2,
        '.',
        ''
    );
}


/**
 * Escape XML.
 */
function xmlValue(?string $value): string
{
    return h((string)$value);
}


// ============================================================
// ID FACTURĂ
// ============================================================

$facturaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$facturaId) {

    http_response_code(400);

    die(
        '<h2>ID factură lipsă</h2>' .
        '<p>Exemplu:</p>' .
        '<pre>EFacturaXML.php?id=1</pre>'
    );
}


// ============================================================
// CITIRE FACTURA
// ============================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM facturi
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $facturaId
]);

$factura = $stmt->fetch();

if (!$factura) {

    http_response_code(404);

    die(
        '<h2>Factura nu există</h2>' .
        '<p>ID: ' .
        htmlspecialchars((string)$facturaId) .
        '</p>'
    );
}


// ============================================================
// CITIRE CLIENT
// ============================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM clienti
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $factura['client_id']
]);

$client = $stmt->fetch();

if (!$client) {

    throw new RuntimeException(
        'Clientul facturii nu există în tabela clienti.'
    );
}


// ============================================================
// DATE FURNIZOR DIN SETARI
// ============================================================

$furnizor = [

    'nume' => getSetting(
        $pdo,
        'efactura_furnizor_nume',
        true
    ),

    'cui' => getSetting(
        $pdo,
        'efactura_furnizor_cui',
        true
    ),

    'reg_com' => getSetting(
        $pdo,
        'efactura_furnizor_reg_com'
    ),

    'adresa' => getSetting(
        $pdo,
        'efactura_furnizor_adresa',
        true
    ),

    'localitate' => getSetting(
        $pdo,
        'efactura_furnizor_localitate',
        true
    ),

    'judet' => getSetting(
        $pdo,
        'efactura_furnizor_judet'
    ),

    'cod_postal' => getSetting(
        $pdo,
        'efactura_furnizor_cod_postal'
    ),

    'telefon' => getSetting(
        $pdo,
        'efactura_furnizor_telefon'
    ),

    'email' => getSetting(
        $pdo,
        'efactura_furnizor_email'
    ),

    'banca' => getSetting(
        $pdo,
        'efactura_furnizor_banca'
    ),

    'iban' => getSetting(
        $pdo,
        'efactura_furnizor_iban'
    )
];


// ============================================================
// VALIDARE CLIENT
// ============================================================

$clientNume = trim((string)$client['nume']);

$clientCui = trim((string)$client['cui']);

$clientAdresa = trim((string)$client['adresa']);

$clientEmail = trim((string)$client['email']);

$clientTelefon = trim((string)$client['telefon']);

$clientLocalitate = '';

$clientJudet = '';

$clientCodPostal = '';


// ------------------------------------------------------------
// Încercăm să extragem localitatea/județul din adresă
// dacă tabela clienti nu are coloane separate.
// ------------------------------------------------------------

if ($clientAdresa !== '') {

    $parts = array_map(
        'trim',
        preg_split(
            '/[,;]+/',
            $clientAdresa
        )
    );

    if (count($parts) >= 2) {

        $clientLocalitate = $parts[count($parts) - 2] ?? '';

        $clientJudet = $parts[count($parts) - 1] ?? '';
    }
}


// ============================================================
// CUI CLIENT
// ============================================================

$clientCuiNormalized = normalizeCui($clientCui);

$clientCuiNumeric = cuiWithoutRo($clientCui);


// ============================================================
// CUI FURNIZOR
// ============================================================

$furnizorCuiNormalized = normalizeCui(
    $furnizor['cui']
);

$furnizorCuiNumeric = cuiWithoutRo(
    $furnizor['cui']
);


// ============================================================
// DATE FACTURA
// ============================================================

$serie = trim((string)$factura['serie']);

$numar = trim((string)$factura['numar']);

$numarFactura = $serie . $numar;

$dataEmitere = date(
    'Y-m-d',
    strtotime((string)$factura['data_emitere'])
);

$dataScadenta = date(
    'Y-m-d',
    strtotime((string)$factura['data_scadenta'])
);

$moneda = strtoupper(
    trim((string)($factura['moneda'] ?: 'RON'))
);

$subtotal = (float)$factura['subtotal'];

$tva = (float)$factura['tva'];

$total = (float)$factura['total'];


// ============================================================
// CALCUL TVA
// ============================================================

if ($subtotal > 0 && $tva > 0) {

    $tvaPercent = round(
        ($tva / $subtotal) * 100,
        2
    );

} else {

    $tvaPercent = 0;
}


// ============================================================
// TIP FACTURA
// ============================================================
//
// 380 = Invoice
//
// ============================================================

$invoiceTypeCode = '380';


// ============================================================
// CITIRE PONTAJ
// ============================================================

$pontaj = null;

if (!empty($factura['numar_timesheet'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM pontaje
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $factura['numar_timesheet']
    ]);

    $pontaj = $stmt->fetch();
}


// ============================================================
// Dacă numar_timesheet nu este ID, încercăm număr factură
// ============================================================

if (!$pontaj && !empty($factura['numar_timesheet'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM pontaje
        WHERE numar_factura = :numar
        AND serie_factura = :serie
        LIMIT 1
    ");

    $stmt->execute([
        ':numar' => $numar,
        ':serie' => $serie
    ]);

    $pontaj = $stmt->fetch();
}


// ============================================================
// DATE PONTAJ
// ============================================================

$zileFacturate = 0;

$numeColaborator = trim(
    (string)$factura['nume_colaborator']
);

$lunaFacturare = '';

if ($pontaj) {

    $zileFacturate = (float)(
        $pontaj['zile_facturate'] ?? 0
    );

    if (!empty($pontaj['nume_prenume'])) {

        $numeColaborator =
            trim((string)$pontaj['nume_prenume']);
    }

    if (!empty($pontaj['luna_facturare'])) {

        $lunaFacturare =
            (string)$pontaj['luna_facturare'];
    }
}


// ============================================================
// DESCRIERE SERVICIU
// ============================================================

$descriere = 'Servicii conform pontaj';

if ($lunaFacturare !== '') {

    $timestampLuna = strtotime($lunaFacturare);

    if ($timestampLuna !== false) {

        $descriere =
            'Servicii conform pontaj - ' .
            date('m/Y', $timestampLuna);
    }
}

if ($numeColaborator !== '') {

    $descriere .=
        ' - ' .
        $numeColaborator;
}


// ============================================================
// CANTITATE
// ============================================================

$cantitate = $zileFacturate;

if ($cantitate <= 0) {
    $cantitate = 1;
}


// ============================================================
// PREȚ UNITAR
// ============================================================

$pretUnitar = $subtotal / $cantitate;


// ============================================================
// XML
// ============================================================

$xml = new DOMDocument(
    '1.0',
    'UTF-8'
);

$xml->formatOutput = true;


// ============================================================
// ROOT
// ============================================================

$invoice = $xml->createElementNS(
    'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
    'Invoice'
);

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

$invoice->setAttributeNS(
    'http://www.w3.org/2000/xmlns/',
    'xmlns:ccts',
    'urn:un:unece:uncefact:documentation:2'
);

$invoice->setAttributeNS(
    'http://www.w3.org/2000/xmlns/',
    'xmlns:qdt',
    'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDataTypes-2'
);

$invoice->setAttributeNS(
    'http://www.w3.org/2000/xmlns/',
    'xmlns:udt',
    'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2'
);

$xml->appendChild($invoice);


// ============================================================
// HELPER XML NODE
// ============================================================

function addNode(
    DOMDocument $xml,
    DOMElement $parent,
    string $name,
    string $value,
    ?string $namespace = null
): DOMElement {

    if ($namespace === null) {
        $namespace =
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    }

    $node = $xml->createElementNS(
        $namespace,
        'cbc:' . $name
    );

    $node->appendChild(
        $xml->createTextNode($value)
    );

    $parent->appendChild($node);

    return $node;
}


function addCac(
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


// ============================================================
// CUSTOMIZATION ID
// ============================================================

addNode(
    $xml,
    $invoice,
    'CustomizationID',
    'urn:cen.eu:en16931:2017#compliant#urn:efactura.mfinante.ro:CIUS-RO:1.0.1'
);


// ============================================================
// PROFILE ID
// ============================================================

addNode(
    $xml,
    $invoice,
    'ProfileID',
    'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0'
);


// ============================================================
// ID FACTURA
// ============================================================

addNode(
    $xml,
    $invoice,
    'ID',
    $numarFactura
);


// ============================================================
// DATA FACTURA
// ============================================================

addNode(
    $xml,
    $invoice,
    'IssueDate',
    $dataEmitere
);


// ============================================================
// TIP FACTURA
// ============================================================

$typeCode = addNode(
    $xml,
    $invoice,
    'InvoiceTypeCode',
    $invoiceTypeCode
);

$typeCode->setAttribute(
    'listID',
    'UNCL1001'
);


// ============================================================
// DATA SCADENTA
// ============================================================

addNode(
    $xml,
    $invoice,
    'DueDate',
    $dataScadenta
);


// ============================================================
// MONEDA
// ============================================================

addNode(
    $xml,
    $invoice,
    'DocumentCurrencyCode',
    $moneda
);


// ============================================================
// FURNIZOR
// ============================================================

$supplierParty = addCac(
    $xml,
    $invoice,
    'AccountingSupplierParty'
);

$supplier = addCac(
    $xml,
    $supplierParty,
    'Party'
);


// ============================================================
// IDENTIFICARE FURNIZOR
// ============================================================

$supplierEndpoint = addNode(
    $xml,
    $supplier,
    'EndpointID',
    $furnizorCuiNumeric
);

$supplierEndpoint->setAttribute(
    'schemeID',
    'EM'
);


// ============================================================
// PARTY IDENTIFICATION
// ============================================================

$supplierIdentification = addCac(
    $xml,
    $supplier,
    'PartyIdentification'
);

addNode(
    $xml,
    $supplierIdentification,
    'ID',
    $furnizorCuiNumeric
);


// ============================================================
// PARTY NAME
// ============================================================

$supplierName = addCac(
    $xml,
    $supplier,
    'PartyName'
);

addNode(
    $xml,
    $supplierName,
    'Name',
    $furnizor['nume']
);


// ============================================================
// ADRESA FURNIZOR
// ============================================================

$supplierAddress = addCac(
    $xml,
    $supplier,
    'PostalAddress'
);

if ($furnizor['adresa'] !== '') {

    addNode(
        $xml,
        $supplierAddress,
        'StreetName',
        $furnizor['adresa']
    );
}

if ($furnizor['localitate'] !== '') {

    addNode(
        $xml,
        $supplierAddress,
        'CityName',
        $furnizor['localitate']
    );
}

if ($furnizor['cod_postal'] !== '') {

    addNode(
        $xml,
        $supplierAddress,
        'PostalZone',
        $furnizor['cod_postal']
    );
}

if ($furnizor['judet'] !== '') {

    addNode(
        $xml,
        $supplierAddress,
        'CountrySubentity',
        'RO-' . $furnizor['judet']
    );
}


// ============================================================
// COUNTRY FURNIZOR
// ============================================================

$supplierCountry = addCac(
    $xml,
    $supplierAddress,
    'Country'
);

addNode(
    $xml,
    $supplierCountry,
    'IdentificationCode',
    'RO'
);


// ============================================================
// TAX SCHEME FURNIZOR
// ============================================================

$supplierTaxScheme = addCac(
    $xml,
    $supplier,
    'PartyTaxScheme'
);

addNode(
    $xml,
    $supplierTaxScheme,
    'CompanyID',
    $furnizorCuiNormalized
);

$taxScheme = addCac(
    $xml,
    $supplierTaxScheme,
    'TaxScheme'
);

addNode(
    $xml,
    $taxScheme,
    'ID',
    'VAT'
);


// ============================================================
// LEGAL ENTITY FURNIZOR
// ============================================================

$supplierLegal = addCac(
    $xml,
    $supplier,
    'PartyLegalEntity'
);

addNode(
    $xml,
    $supplierLegal,
    'RegistrationName',
    $furnizor['nume']
);

if ($furnizor['reg_com'] !== '') {

    addNode(
        $xml,
        $supplierLegal,
        'CompanyID',
        $furnizor['reg_com']
    );
}


// ============================================================
// CONTACT FURNIZOR
// ============================================================

if (
    $furnizor['telefon'] !== '' ||
    $furnizor['email'] !== ''
) {

    $contact = addCac(
        $xml,
        $supplier,
        'Contact'
    );

    if ($furnizor['telefon'] !== '') {

        addNode(
            $xml,
            $contact,
            'Telephone',
            $furnizor['telefon']
        );
    }

    if ($furnizor['email'] !== '') {

        addNode(
            $xml,
            $contact,
            'ElectronicMail',
            $furnizor['email']
        );
    }
}


// ============================================================
// CUMPĂRĂTOR
// ============================================================

$customerParty = addCac(
    $xml,
    $invoice,
    'AccountingCustomerParty'
);

$customer = addCac(
    $xml,
    $customerParty,
    'Party'
);


// ============================================================
// ENDPOINT CLIENT
// ============================================================

if ($clientCuiNumeric !== '') {

    $customerEndpoint = addNode(
        $xml,
        $customer,
        'EndpointID',
        $clientCuiNumeric
    );

    $customerEndpoint->setAttribute(
        'schemeID',
        'EM'
    );
}


// ============================================================
// IDENTIFICATION CLIENT
// ============================================================

if ($clientCuiNumeric !== '') {

    $customerIdentification = addCac(
        $xml,
        $customer,
        'PartyIdentification'
    );

    addNode(
        $xml,
        $customerIdentification,
        'ID',
        $clientCuiNumeric
    );
}


// ============================================================
// NUME CLIENT
// ============================================================

$customerName = addCac(
    $xml,
    $customer,
    'PartyName'
);

addNode(
    $xml,
    $customerName,
    'Name',
    $clientNume
);


// ============================================================
// ADRESA CLIENT
// ============================================================

$customerAddress = addCac(
    $xml,
    $customer,
    'PostalAddress'
);

if ($clientAdresa !== '') {

    addNode(
        $xml,
        $customerAddress,
        'StreetName',
        $clientAdresa
    );
}

if ($clientLocalitate !== '') {

    addNode(
        $xml,
        $customerAddress,
        'CityName',
        $clientLocalitate
    );
}

if ($clientCodPostal !== '') {

    addNode(
        $xml,
        $customerAddress,
        'PostalZone',
        $clientCodPostal
    );
}

if ($clientJudet !== '') {

    addNode(
        $xml,
        $customerAddress,
        'CountrySubentity',
        'RO-' . $clientJudet
    );
}


// ============================================================
// COUNTRY CLIENT
// ============================================================

$customerCountry = addCac(
    $xml,
    $customerAddress,
    'Country'
);

addNode(
    $xml,
    $customerCountry,
    'IdentificationCode',
    'RO'
);


// ============================================================
// TAX SCHEME CLIENT
// ============================================================

$customerTaxScheme = addCac(
    $xml,
    $customer,
    'PartyTaxScheme'
);

if ($clientCuiNumeric !== '') {

    addNode(
        $xml,
        $customerTaxScheme,
        'CompanyID',
        $clientCuiNormalized
    );
}

$customerTaxSchemeNode = addCac(
    $xml,
    $customerTaxScheme,
    'TaxScheme'
);

addNode(
    $xml,
    $customerTaxSchemeNode,
    'ID',
    'VAT'
);


// ============================================================
// LEGAL ENTITY CLIENT
// ============================================================

$customerLegal = addCac(
    $xml,
    $customer,
    'PartyLegalEntity'
);

addNode(
    $xml,
    $customerLegal,
    'RegistrationName',
    $clientNume
);


// ============================================================
// CONTACT CLIENT
// ============================================================

if (
    $clientTelefon !== '' ||
    $clientEmail !== ''
) {

    $customerContact = addCac(
        $xml,
        $customer,
        'Contact'
    );

    if ($clientTelefon !== '') {

        addNode(
            $xml,
            $customerContact,
            'Telephone',
            $clientTelefon
        );
    }

    if ($clientEmail !== '') {

        addNode(
            $xml,
            $customerContact,
            'ElectronicMail',
            $clientEmail
        );
    }
}


// ============================================================
// PAYMENT MEANS
// ============================================================

$paymentMeans = addCac(
    $xml,
    $invoice,
    'PaymentMeans'
);

addNode(
    $xml,
    $paymentMeans,
    'PaymentMeansCode',
    '30'
);


// ============================================================
// CONT BANCAR FURNIZOR
// ============================================================

if ($furnizor['iban'] !== '') {

    $payeeAccount = addCac(
        $xml,
        $paymentMeans,
        'PayeeFinancialAccount'
    );

    addNode(
        $xml,
        $payeeAccount,
        'ID',
        $furnizor['iban']
    );

    if ($furnizor['banca'] !== '') {

        $financialInstitution = addCac(
            $xml,
            $payeeAccount,
            'FinancialInstitution'
        );

        addNode(
            $xml,
            $financialInstitution,
            'Name',
            $furnizor['banca']
        );
    }
}


// ============================================================
// TAX TOTAL
// ============================================================

$taxTotal = addCac(
    $xml,
    $invoice,
    'TaxTotal'
);

$taxAmount = addNode(
    $xml,
    $taxTotal,
    'TaxAmount',
    money($tva)
);

$taxAmount->setAttribute(
    'currencyID',
    $moneda
);


// ============================================================
// TAX SUBTOTAL
// ============================================================

$taxSubtotal = addCac(
    $xml,
    $taxTotal,
    'TaxSubtotal'
);

$taxableAmount = addNode(
    $xml,
    $taxSubtotal,
    'TaxableAmount',
    money($subtotal)
);

$taxableAmount->setAttribute(
    'currencyID',
    $moneda
);

$taxSubtotalAmount = addNode(
    $xml,
    $taxSubtotal,
    'TaxAmount',
    money($tva)
);

$taxSubtotalAmount->setAttribute(
    'currencyID',
    $moneda
);


// ============================================================
// TVA CATEGORY
// ============================================================

$taxCategory = addCac(
    $xml,
    $taxSubtotal,
    'TaxCategory'
);

addNode(
    $xml,
    $taxCategory,
    'ID',
    $tva > 0 ? 'S' : 'Z'
);

$percentNode = addNode(
    $xml,
    $taxCategory,
    'Percent',
    money($tvaPercent)
);

$taxCategoryScheme = addCac(
    $xml,
    $taxCategory,
    'TaxScheme'
);

addNode(
    $xml,
    $taxCategoryScheme,
    'ID',
    'VAT'
);


// ============================================================
// LEGAL MONETARY TOTAL
// ============================================================

$monetaryTotal = addCac(
    $xml,
    $invoice,
    'LegalMonetaryTotal'
);


// LINE EXTENSION AMOUNT

$lineExtension = addNode(
    $xml,
    $monetaryTotal,
    'LineExtensionAmount',
    money($subtotal)
);

$lineExtension->setAttribute(
    'currencyID',
    $moneda
);


// TAX EXCLUSIVE

$taxExclusive = addNode(
    $xml,
    $monetaryTotal,
    'TaxExclusiveAmount',
    money($subtotal)
);

$taxExclusive->setAttribute(
    'currencyID',
    $moneda
);


// TAX INCLUSIVE

$taxInclusive = addNode(
    $xml,
    $monetaryTotal,
    'TaxInclusiveAmount',
    money($total)
);

$taxInclusive->setAttribute(
    'currencyID',
    $moneda
);


// PAYABLE

$payable = addNode(
    $xml,
    $monetaryTotal,
    'PayableAmount',
    money($total)
);

$payable->setAttribute(
    'currencyID',
    $moneda
);


// ============================================================
// FACTURA LINE
// ============================================================

$invoiceLine = addCac(
    $xml,
    $invoice,
    'InvoiceLine'
);


// ID LINIE

addNode(
    $xml,
    $invoiceLine,
    'ID',
    '1'
);


// CANTITATE

$quantity = addNode(
    $xml,
    $invoiceLine,
    'InvoicedQuantity',
    number_format(
        $cantitate,
        2,
        '.',
        ''
    )
);

$quantity->setAttribute(
    'unitCode',
    'DAY'
);


// VALOARE LINIE

$lineAmount = addNode(
    $xml,
    $invoiceLine,
    'LineExtensionAmount',
    money($subtotal)
);

$lineAmount->setAttribute(
    'currencyID',
    $moneda
);


// ============================================================
// ITEM
// ============================================================

$item = addCac(
    $xml,
    $invoiceLine,
    'Item'
);

addNode(
    $xml,
    $item,
    'Name',
    $descriere
);


// CLASSIFIED TAX

$itemTaxCategory = addCac(
    $xml,
    $item,
    'ClassifiedTaxCategory'
);

addNode(
    $xml,
    $itemTaxCategory,
    'ID',
    $tva > 0 ? 'S' : 'Z'
);

addNode(
    $xml,
    $itemTaxCategory,
    'Percent',
    money($tvaPercent)
);

$itemTaxScheme = addCac(
    $xml,
    $itemTaxCategory,
    'TaxScheme'
);

addNode(
    $xml,
    $itemTaxScheme,
    'ID',
    'VAT'
);


// ============================================================
// PRICE
// ============================================================

$price = addCac(
    $xml,
    $invoiceLine,
    'Price'
);

$priceAmount = addNode(
    $xml,
    $price,
    'PriceAmount',
    money($pretUnitar)
);

$priceAmount->setAttribute(
    'currencyID',
    $moneda
);


// ============================================================
// VALIDARE XML
// ============================================================

$xmlContent = $xml->saveXML();

if ($xmlContent === false) {

    throw new RuntimeException(
        'Nu s-a putut genera XML-ul.'
    );
}


// ============================================================
// CREARE DIRECTOR
// ============================================================

if (!is_dir($xmlDirectory)) {

    if (!mkdir(
        $xmlDirectory,
        0775,
        true
    )) {

        throw new RuntimeException(
            'Nu s-a putut crea directorul XML: ' .
            $xmlDirectory
        );
    }
}


// ============================================================
// NUME FIȘIER
// ============================================================

$safeSerie = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    $serie
);

$safeNumar = preg_replace(
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


// ============================================================
// SALVARE XML
// ============================================================

if (
    file_put_contents(
        $filePath,
        $xmlContent,
        LOCK_EX
    ) === false
) {

    throw new RuntimeException(
        'Nu s-a putut salva XML-ul: ' .
        $filePath
    );
}


// ============================================================
// PATH RELATIV
// ============================================================

$relativePath =
    'storage/facturi/xml/' .
    $fileName;


// ============================================================
// UPDATE FACTURA
// ============================================================

$stmt = $pdo->prepare("
    UPDATE facturi
    SET
        xml_path = :xml_path,
        xml_status = :xml_status,
        xml_generated_at = NOW()
    WHERE id = :id
");

$stmt->execute([
    ':xml_path' => $relativePath,
    ':xml_status' => 'generat',
    ':id' => $facturaId
]);


// ============================================================
// AFIȘARE REZULTAT
// ============================================================

?>
<!DOCTYPE html>
<html lang="ro">

<head>

    <meta charset="UTF-8">

    <title>
        XML e-Factura generat
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,.08);
        }

        h1 {
            color: #198754;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 8px;
        }

        .btn:hover {
            background: #0b5ed7;
        }

        pre {
            background: #111;
            color: #eee;
            padding: 20px;
            overflow: auto;
            max-height: 600px;
            border-radius: 6px;
            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>✓ XML e-Factura generat</h1>

    <div class="success">

        XML-ul facturii a fost generat cu succes.

    </div>

    <div class="info">

        <strong>Factura:</strong>
        <?= htmlspecialchars($numarFactura) ?>

        <br><br>

        <strong>Client:</strong>
        <?= htmlspecialchars($clientNume) ?>

        <br><br>

        <strong>Subtotal:</strong>
        <?= htmlspecialchars(money($subtotal)) ?>
        <?= htmlspecialchars($moneda) ?>

        <br><br>

        <strong>TVA:</strong>
        <?= htmlspecialchars(money($tva)) ?>
        <?= htmlspecialchars($moneda) ?>

        <br><br>

        <strong>Total:</strong>
        <?= htmlspecialchars(money($total)) ?>
        <?= htmlspecialchars($moneda) ?>

        <br><br>

        <strong>Fișier:</strong>
        <?= htmlspecialchars($fileName) ?>

    </div>


    <a
        class="btn"
        href="<?= htmlspecialchars($relativePath) ?>"
        download
    >
        Descarcă XML
    </a>


    <a
        class="btn"
        href="<?= htmlspecialchars($relativePath) ?>"
        target="_blank"
    >
        Vezi XML
    </a>


    <h3>XML generat</h3>

    <pre><?= htmlspecialchars($xmlContent) ?></pre>

</div>

</body>

</html>