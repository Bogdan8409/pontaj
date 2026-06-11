<?php
$conn = new mysqli("localhost","root","","2web_pontaj");

$limit  = $_GET['length'];
$start  = $_GET['start'];
$order  = $_GET['order'][0]['column'];
$dir    = $_GET['order'][0]['dir'];
$search = $_GET['search']['value'];

$columns = [
  'id','data_trimiterii','colaborator','luna',
  'client','zile_facturate','factura_numar','status'
];

$where = $search ? "WHERE colaborator LIKE '%$search%' OR client LIKE '%$search%'" : "";

$total = $conn->query("SELECT COUNT(*) c FROM pontaje")->fetch_assoc()['c'];

$query = "
SELECT * FROM pontaje
$where
ORDER BY {$columns[$order]} $dir
LIMIT $start, $limit
";

$data = [];
$res = $conn->query($query);

while($row = $res->fetch_assoc()){
  $data[] = [
    'checkbox' => '<input type="checkbox" class="row-check" value="'.$row['id'].'">',
    'id' => '#'.$row['id'],
    'data_trimiterii' => $row['data_trimiterii'],
    'colaborator' => $row['colaborator'],
    'luna' => $row['luna'],
    'client' => $row['client'],
    'zile_facturate' => $row['zile_facturate'],
    'factura' => $row['factura_serie'].' / '.$row['factura_numar'],
    'status' => badge($row['status']),
    'actiuni' => '
      <a href="pontaj.php?id='.$row['id'].'" class="btn btn-info btn-sm">Vezi</a>
      <button class="btn btn-success btn-sm">Aproba</button>
      <button class="btn btn-danger btn-sm">Respinge</button>
    '
  ];
}

function badge($status){
  return match($status){
    'Aprobat' => '<span class="badge bg-success">Aprobat</span>',
    'Respins' => '<span class="badge bg-danger">Respins</span>',
    default => '<span class="badge bg-warning">Pending</span>',
  };
}

echo json_encode([
  "draw" => intval($_GET['draw']),
  "recordsTotal" => $total,
  "recordsFiltered" => $total,
  "data" => $data
]);