<?php
if (!isset($_GET['id'])) {
  header("Location: /2web_pontaj/admin/pontaje.php");
  exit;
}
$conn = new mysqli("localhost", "root", "", "2web_pontaj");

$id = $_GET['id'] ?? null;



if ($id) {
  // 👉 DETALII PENTRU UN PONTAJ
  if (!ctype_digit($id)) {
    die('ID invalid');
  }

  $stmt = $conn->prepare("SELECT * FROM pontaje WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $pontaj = $stmt->get_result()->fetch_assoc();

  if (!$pontaj) {
    die('Pontaj inexistent');
  }

  // afișezi detalii pontaj
} else {
  // 👉 PAGINA GENERALĂ (fără id)
  // afișezi ce vrei tu: listă, mesaj, dashboard etc.
}

$sql = "SELECT 
            p.*,
            c.nume AS client_nume
        FROM pontaje p
        LEFT JOIN clienti c ON p.client_id = c.id
        ORDER BY p.data_trimitere DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Pontaj #<?= $pontaj['id'] ?></h1>
  </section>
  <section class="content">
    <div class="card">
      <div class="card-body">
        <table class="table table-bordered">
          <tr>
            <th>ID</th>
            <td><?= $pontaj['id'] ?></td>
          </tr>
          <tr>
            <th>Colaborator</th>
            <td><?= htmlspecialchars($pontaj['nume_prenume']) ?></td>
          </tr>
          <tr>
            <th>Luna</th>
            <td><?= $pontaj['luna_facturare'] ?></td>
          </tr>
          <tr>
            <th>Client</th>
            <td><?= htmlspecialchars($row['client_nume'] ?? '-') ?></td>

          </tr>
          <tr>
            <th>Zile</th>
            <td><?= $pontaj['zile_facturate'] ?></td>
          </tr>
          <tr>
            <th>Factura</th>
            <td><?= $pontaj['serie_factura'] ?> / <?= $pontaj['numar_factura'] ?></td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <?= match ($pontaj['status']) {
                'Aprobat' => '<span class="badge bg-success">Aprobat</span>',
                'Respins' => '<span class="badge bg-danger">Respins</span>',
                default => '<span class="badge bg-warning">Pending</span>'
              } ?>
            </td>
          </tr>
        </table>
      </div>
      <div class="card-footer text-end">
        <a href="/2web_pontaj/admin/pontaje.php" class="btn btn-secondary">
          Înapoi
        </a>
        <?php if ($pontaj['status'] === 'Pending'): ?>
          <form method="POST" action="/admin/actions/approve_pontaj.php" style="display:inline-block;">
            <input type="hidden" name="id" value="<?= $pontaj['id'] ?>">
            <button class="btn btn-success"><i class="fas fa-check"></i> Aprobă</button>
          </form>
          <form method="POST" action="/admin/actions/respinge_pontaj.php" style="display:inline-block;">
            <input type="hidden" name="id" value="<?= $pontaj['id'] ?>">
            <button class="btn btn-danger"><i class="fas fa-times"></i> Respinge</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>