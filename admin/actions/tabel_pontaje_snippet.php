<?php
/*
 * ─────────────────────────────────────────────────────────────
 *  SNIPPET – Tabel pontaje cu coloana Factura + Actiuni
 *
 *  Presupune ca $result vine dintr-un query de tipul:
 *
 *  SELECT p.id, p.data_trimitere, p.luna_facturare,
 *         p.nume_prenume, p.zile_facturate,
 *         p.serie_factura, p.numar_factura,
 *         p.status, p.motiv_respingere,
 *         p.factura_pdf_path,
 *         c.nume AS client_nume
 *  FROM pontaje p
 *  JOIN clienti c ON p.client_id = c.id
 *  ORDER BY p.data_trimitere DESC
 * ─────────────────────────────────────────────────────────────
 */
?>

<!-- Mesaj succes dupa generare factura -->
<?php if (isset($_GET['succes']) && $_GET['succes'] === 'factura_generata'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i>
        Factura a fost generată și salvată cu succes!
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<table id="pontajeTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#ID</th>
            <th>Data Trimitere</th>
            <th>Colaborator</th>
            <th>Luna Facturare</th>
            <th>Client</th>
            <th>Zile Facturate</th>
            <th>Factură</th>
            <th>Status</th>
            <th>Acțiuni</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <!-- ID -->
                <td><?= htmlspecialchars($row['id']) ?></td>

                <!-- Data trimitere -->
                <td><?= htmlspecialchars($row['data_trimitere']) ?></td>

                <!-- Colaborator -->
                <td><?= htmlspecialchars($row['nume_prenume'] ?? '-') ?></td>

                <!-- Luna facturare -->
                <td><?= htmlspecialchars($row['luna_facturare'] ?? '-') ?></td>

                <!-- Client -->
                <td><?= htmlspecialchars($row['client_nume'] ?? '-') ?></td>

                <!-- Zile facturate -->
                <td><?= htmlspecialchars($row['zile_facturate']) ?></td>

                <!-- Coloana FACTURA – arata PDF daca exista, altfel badge Lipsa -->
                <td>
                    <?php if (!empty($row['factura_pdf_path'])): ?>
                        <a href="/facturi/<?= htmlspecialchars(rawurlencode(basename($row['factura_pdf_path']))) ?>"
                           target="_blank"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-file-pdf"></i> Vezi
                        </a>
                    <?php else: ?>
                        <span class="badge badge-secondary">Lipsă</span>
                    <?php endif; ?>
                </td>

                <!-- Coloana STATUS cu badge colorat -->
                <td>
                    <?php
                        $status = strtolower($row['status'] ?? '');
                        $badge  = match($status) {
                            'aprobat' => 'badge-success',
                            'respins' => 'badge-danger',
                            default   => 'badge-warning',
                        };
                    ?>
                    <span class="badge <?= $badge ?>">
                        <?= htmlspecialchars($row['status']) ?>
                    </span>
                    <?php if ($status === 'respins' && !empty($row['motiv_respingere'])): ?>
                        <br>
                        <small class="text-danger">
                            <i class="fas fa-info-circle"></i>
                            <?= htmlspecialchars($row['motiv_respingere']) ?>
                        </small>
                    <?php endif; ?>
                </td>

                <!-- Coloana ACTIUNI -->
                <td>
                    <div class="d-flex flex-wrap gap-1">

                        <?php
                            $este_aprobat        = strcasecmp($row['status'], 'aprobat') === 0;
                            $are_factura         = !empty($row['factura_pdf_path']);
                            $este_pending        = strcasecmp($row['status'], 'pending') === 0;
                            $este_respins        = strcasecmp($row['status'], 'respins') === 0;
                        ?>

                        <?php if ($este_aprobat && !$are_factura): ?>
                            <!-- Buton Genereaza Factura -->
                            <form method="post" action="actions/genereaza_factura.php">
                                <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="redirect"  value="pontaje.php">
                                <button type="submit" class="btn btn-success btn-sm"
                                        title="Generează PDF factură și salvează">
                                    <i class="fas fa-file-invoice"></i> Generează
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($are_factura): ?>
                            <!-- Descarca PDF -->
                            <a href="/facturi/<?= htmlspecialchars(rawurlencode(basename($row['factura_pdf_path']))) ?>"
                               download="<?= htmlspecialchars(basename($row['factura_pdf_path'])) ?>"
                               class="btn btn-outline-primary btn-sm"
                               title="Descarcă factura PDF">
                                <i class="fas fa-download"></i>
                            </a>
                        <?php endif; ?>

                        <?php if ($este_pending): ?>
                            <!-- Aprobare rapida -->
                            <form method="post" action="actions/aprobare_pontaj.php">
                                <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="redirect"  value="pontaje.php">
                                <button type="submit" class="btn btn-success btn-sm"
                                        title="Aprobă pontajul"
                                        onclick="return confirm('Aprobați pontajul #<?= $row['id'] ?>?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>

                            <!-- Respingere cu motiv -->
                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    title="Respinge pontajul"
                                    data-toggle="modal"
                                    data-target="#modalRespingere"
                                    data-pontaj-id="<?= $row['id'] ?>"
                                    data-colaborator="<?= htmlspecialchars($row['nume_prenume'] ?? '') ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php endif; ?>

                        <!-- Vizualizare detalii -->
                        <a href="detalii_pontaj.php?id=<?= $row['id'] ?>"
                           class="btn btn-info btn-sm"
                           title="Vezi detalii pontaj">
                            <i class="fas fa-eye"></i>
                        </a>

                        <?php if ($este_respins): ?>
                            <!-- Resetare la pending pentru retrimitere -->
                            <form method="post" action="actions/reseteaza_pontaj.php">
                                <input type="hidden" name="pontaj_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="redirect"  value="pontaje.php">
                                <button type="submit" class="btn btn-warning btn-sm"
                                        title="Resetează la Pending"
                                        onclick="return confirm('Resetați pontajul #<?= $row['id'] ?> la Pending?')">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>


<!-- ============================================================ -->
<!-- MODAL RESPINGERE cu motiv                                    -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRespingere" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="actions/respinge_pontaj.php">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle mr-1"></i>
                        Respingere Pontaj
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="pontaj_id" id="modalPontajId">
                    <input type="hidden" name="redirect"  value="pontaje.php">

                    <p>
                        Respingeți pontajul colaboratorului
                        <strong id="modalColaborator"></strong>?
                    </p>

                    <div class="form-group">
                        <label for="motivRespingere">
                            Motiv respingere <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="motivRespingere"
                                  name="motiv_respingere" rows="3"
                                  placeholder="Explicați motivul respingerii..."
                                  required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Anulează
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i> Respinge
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<!-- Script pentru modal respingere -->
<script>
$('#modalRespingere').on('show.bs.modal', function (event) {
    const btn          = $(event.relatedTarget);
    const pontajId     = btn.data('pontaj-id');
    const colaborator  = btn.data('colaborator');

    $('#modalPontajId').val(pontajId);
    $('#modalColaborator').text(colaborator);
    $('#motivRespingere').val('');
});
</script>
