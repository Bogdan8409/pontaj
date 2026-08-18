<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/EFacturaXML.php';

try {

    /*
     * ID FACTURĂ DE TEST
     *
     * SCHIMBĂ 1 CU ID-UL FACTURII TALE
     */
    $facturaId = 1;

    $generator = new EFacturaXML($pdo);

    $path = $generator->generate(
        $facturaId
    );

    echo '<h2>XML generat cu succes!</h2>';

    echo '<p>';
    echo 'Fișier: ';
    echo htmlspecialchars($path);
    echo '</p>';

    /*
     * Determinăm calea relativă
     */
    $relativePath =
        str_replace(
            __DIR__ . DIRECTORY_SEPARATOR,
            '',
            $path
        );

    echo '<p>';

    echo '<a href="' .
        htmlspecialchars(
            $relativePath
        ) .
        '" target="_blank">';

    echo 'Deschide XML';

    echo '</a>';

    echo '</p>';

} catch (Throwable $e) {

    http_response_code(500);

    echo '<h2>Eroare la generarea XML</h2>';

    echo '<pre>';
    echo htmlspecialchars(
        $e->getMessage()
    );
    echo '</pre>';
}