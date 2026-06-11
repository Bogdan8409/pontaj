<?php
if ($actiune == 'respinge') {
   $subject = "[PONTAJ RESPINS] $luna - Actiune necesara";
$mail->isHTML(true);
$mail->Subject = $subject;

$mail->Body = "
<h2>Pontaj respins</h2>

<p>Buna ziua <b>$numeColaborator</b>,</p>

<p>Pontajul dumneavoastra pentru luna <b>$luna</b> a fost <b>RESPINS</b> si necesita corecturi.</p>

<p><b>Detalii pontaj:</b></p>
<ul>
  <li><b>Client:</b> $numeClient</li>
  <li><b>Zile rezervate:</b> $zileRezervate</li>
  <li><b>Zile facturate:</b> $zileFacturate</li>
  <li><b>Respins de:</b> $numeAdministrator</li>
  <li><b>Data respingerii:</b> ".date('d.m.Y H:i')."</li>
</ul>

<p><b>Motiv respingere:</b></p>
<p>$motivRespingere</p>

<p>
<a href='$linkUtilizator'>Corecteaza pontajul</a>
</p>

<p>Cu stima,<br>
Sistem Gestionare Pontaje<br>
2WEB SOFTWARE SRL</p>
";

$mail->AltBody = "
Pontaj respins

Colaborator: $numeColaborator
Luna: $luna
Client: $numeClient
Zile rezervate: $zileRezervate
Zile facturate: $zileFacturate
Respins de: $numeAdministrator
Data: ".date('d.m.Y H:i')."

Motiv respingere:
$motivRespingere

$linkUtilizator
";
}
?>