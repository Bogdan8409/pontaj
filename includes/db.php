<?php
  // Conexiune la baza de date
  $host = "localhost";
  $user = "root";
  $pass = "";
  $db = "2web_pontaj";

  $conn = new mysqli($host, $user, $pass, $db);
  if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
  }

  // Interogăm tabela clienti
  $sql = "SELECT id, nume FROM clienti ORDER BY nume ASC";
  $result = $conn->query($sql);

  // Generăm opțiunile dropdown
  $options = "";
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nume']) . '</option>';
    }
  }
  $conn->close();


  ?>