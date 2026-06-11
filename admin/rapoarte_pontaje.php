
<?php if (isset($_GET['factura'])): ?>
<div class="alert alert-success">
    Factura generată:
    <a href="/facturi/<?= htmlspecialchars(rawurlencode(basename($_GET['factura']))) ?>" target="_blank">
        Descarcă PDF
    </a>
</div>
<?php endif; ?>

<?php
require_once('../conexiune.php');

$sql = "SELECT p.*, c.nume AS client_nume
        FROM pontaje p
        LEFT JOIN clienti c ON p.client = c.id
        ORDER BY p.id DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Rapoarte Pontaje</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Rapoarte Pontaje</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Colaborator</th>
                <th>Luna</th>
                <th>Client</th>
                <th>Zile</th>
                <th>Acțiune</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['colaborator']) ?></td>
                <td><?= htmlspecialchars($p['luna']) ?></td>
                <td><?= htmlspecialchars($p['client_nume']) ?></td>
                <td><?= $p['zile_facturate'] ?></td>
                <td>
                    <!-- 🔥 BUTONUL POST -->
                    <form action="actions/genereaza_factura.php" method="post">
                        <input type="hidden" name="pontaj_id" value="<?= $p['id'] ?>">
                        <button class="btn btn-success btn-sm">
                            Generează Factura
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
