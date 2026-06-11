<?php

$conn = new mysqli("localhost","root","","2web_pontaj");

if ($conn->connect_error) {
    die("Conexiune esuata: " . $conn->connect_error);
}

if(!isset($_GET['id'])){
    die("ID lipsa");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM clienti WHERE id=?");
$stmt->bind_param("i",$id);

if($stmt->execute()){
    
    header("Location: setari.php?msg=sters");
    exit;

}else{

    echo "Eroare stergere: " . $stmt->error;

}

?>