<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


header('Content-Type: application/json');

// $data = json_decode(file_get_contents("php://input"), true);
$data = $_POST;
if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Nu s-au primit date"
    ]);
    exit;
    }



$client         = $data['client'] ?? 'Nume lipsă';
$serie_factura  = $data['serie_factura'] ?? 'Serie lipsă';
$zile_facturate = $data['zile_facturate'] ?? '0';
$zile_rezervate = $data['zile_rezervate'] ?? '0';

// configure PHPMailer to use local MailHog SMTP
$mail = new PHPMailer(true);
$response = [
    "success" => false,
    "message" => "Unknown error"
];
function sendEmail($mail, $email, $client, $serie_factura, $zile_facturate, $zile_rezervate) {
    $mail = new PHPMailer(true);
try {
    // MailHog listens on localhost:1025 by default
    $mail->isSMTP();
    $mail->Host       = '127.0.0.1';
    $mail->Port       = 1025;
    $mail->SMTPAutoTLS = false; // MailHog doesn't support STARTTLS

    // set sender and recipient
    $mail->setFrom('no-reply@example.local', 'Pontaj System');
    $mail->addAddress('recipient@example.local', $email);

    // compose message body
    $mail->isHTML(true);
    $mail->Subject = "Factura $serie_factura pentru $client";
    $mail->Body    = "<p>Salut $client,</p>\n" .
                     "<p>Avem următoarele zile facturate: <strong>$zile_facturate</strong> " .
                     "și rezervate: <strong>$zile_rezervate</strong>.</p>";
    $mail->AltBody = "Salut $client,\n" .
                     "Zile facturate: $zile_facturate, " .
                     "zile rezervate: $zile_rezervate.";

    $mail->send();
    $response = [
        "success" => true,
        "message" => "Email trimis cu succes"
    ];
    return true;
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => "Mailer Error: {$mail->ErrorInfo}"
    ];
    return false;
}

};
echo json_encode($response);
?>