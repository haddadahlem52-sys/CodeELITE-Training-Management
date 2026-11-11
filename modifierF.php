<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$host = 'localhost';
$db   = 'centredeformations';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

$message      = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message      = "Erreur de sécurité : token invalide. Veuillez recharger la page.";
        $type_message = 'danger';
    } else {

        $action = $_POST['action'] ?? '';

        if ($action === 'supprimer') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                $message = "Identifiant invalide."; $type_message = 'danger';
            } else {
                $check = $pdo->prepare("SELECT id FROM formations WHERE id = :id");
                $check->execute([':id' => $id]);
                if (!$check->fetch()) {
                    $message = "Formation introuvable."; $type_message = 'danger';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM formations WHERE id = :id");
                        $stmt->execute([':id' => $id]);
                        $message = "La formation a été supprimée avec succès."; $type_message = 'success';
                    } catch (PDOException $e) {
                        $message = "Erreur lors de la suppression : " . $e->getMessage(); $type_message = 'danger';
                    }
                }
            }
        }

        if ($action === 'modifier') {
            $id           = (int)($_POST['id']           ?? 0);
            $titre        = trim($_POST['titre']         ?? '');
            $description  = trim($_POST['description']   ?? '');
            $prerequis    = trim($_POST['prerequis']     ?? '');
            $type         = trim($_POST['type']          ?? '');
            $duree        = (int)($_POST['duree']        ?? 0);
            $id_formateur = (int)($_POST['id_formateur'] ?? 0);
            $id_salle     = !empty($_POST['id_salle']) ? (int)$_POST['id_salle'] : null;
            $date_debut   = trim($_POST['date_debut']    ?? '');
            $date_fin     = trim($_POST['date_fin']      ?? '');

            if ($titre === '' || $duree < 1 || $id_formateur < 1 || $date_debut === '' || $date_fin === '') {
                $message = "Veuillez remplir tous les champs obligatoires."; $type_message = 'danger';
            } elseif ($date_fin < $date_debut) {
                $message = "La date de fin doit être après la date de début."; $type_message = 'danger';
            } elseif (!in_array($type, ['Presentiel', 'enligne'], true)) {
                $message = "Type de formation invalide."; $type_message = 'danger';
            } else {
                try {
                    $sql = "UPDATE formations SET titre=:titre, description=:description, prerequis=:prerequis,
                            type=:type, duree=:duree, id_formateur=:id_formateur, id_salle=:id_salle,
                            date_debut=:date_debut, date_fin=:date_fin WHERE id=:id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':titre' => $titre, ':description' => $description ?: null,
                        ':prerequis' => $prerequis ?: null, ':type' => $type, ':duree' => $duree,
                        ':id_formateur' => $id_formateur, ':id_salle' => $id_salle,
                        ':date_debut' => $date_debut, ':date_fin' => $date_fin, ':id' => $id,
                    ]);
                    $message = "La formation a été modifiée avec succès."; $type_message = 'success';
                } catch (PDOException $e) {
                    $message = "Erreur lors de la modification : " . $e->getMessage(); $type_message = 'danger';
                }
            }
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrf = $_SESSION['csrf_token'];
    }
}

$formations = $pdo->query("SELECT * FROM formations ORDER BY date_debut DESC")->fetchAll(PDO::FETCH_ASSOC);

function tronquer(string $texte, int $max = 60): string {
    if (function_exists('mb_strimwidth')) return mb_strimwidth($texte, 0, $max, '…');
    return strlen($texte) > $max ? substr($texte, 0, $max) . '…' : $texte;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des Formations</title>
    <style>
        :root {
            --beige:        #f5f1e6;
            --beige-mid:    #e8e2d4;
            --beige-dark:   #d6c7a1;
            --noir:         #1a1a1a;
            --noir-light:   #2e2e2e;
            --blanc:        #ffffff;
            --gris-texte:   #5a5a5a;
            --gris-bordure: #ddd8ce;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: var(--beige);
            color: var(--noir);
            min-height: 100vh;
            padding: 48px 24px;
        }

        /* ===== LAYOUT ===== */
        .page-wrap {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* ===== EN-TÊTE ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gris-bordure);
        }

        .page-header h1 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--noir);
            margin-bottom: 4px;
        }

        .page-header .sous-titre {
            font-size: 13px;
            color: var(--gris-texte);
            font-style: italic;
        }

        /* ===== MESSAGES ===== */
        .msg {
            padding: 11px 18px;
            border-radius: 2px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .msg-success {
            background: var(--beige-mid);
            color: var(--noir);
            border: 1px solid var(--beige-dark);
        }
        .msg-danger {
            background: #f0e6e6;
            color: #4a1a1a;
            border: 1px solid #dfc8c8;
        }

        /* ===== BOUTON PRINCIPAL ===== */
        .btn-primary {
            background: var(--noir);
            color: var(--blanc);
            border: none;
            padding: 10px 22px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.15s;
        }
        .btn-primary:hover { background: var(--noir-light); }

        /* ===== TABLEAU ===== */
        .table-wrap {
            background: var(--blanc);
            border: 1px solid var(--gris-bordure);
            border-radius: 2px;
            overflow: hidden;
        }

        .table-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--gris-bordure);
            background: var(--beige-mid);
        }

        .table-header span {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--noir);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background: var(--beige-mid);
            color: var(--noir);
            font-weight: bold;
            padding: 11px 14px;
            text-align: left;
            border-bottom: 1px solid var(--gris-bordure);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #ece7de;
            color: var(--noir);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--beige); transition: background 0.1s; }

        .td-titre .titre-nom { font-weight: bold; font-size: 13px; }
        .td-titre .titre-desc { font-size: 12px; color: var(--gris-texte); margin-top: 2px; }
        .td-muted { color: var(--gris-texte); font-size: 12px; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 1px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-presentiel {
            background: var(--noir);
            color: var(--blanc);
        }
        .badge-enligne {
            background: var(--beige-mid);
            color: var(--noir);
            border: 1px solid var(--gris-bordure);
        }

        /* ===== BOUTONS D'ACTION ===== */
        .btn-actions {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .btn-action {
            width: 30px;
            height: 30px;
            border-radius: 2px;
            border: 1px solid var(--gris-bordure);
            background: var(--blanc);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: background 0.12s, border-color 0.12s;
        }

        .btn-edit:hover  { background: var(--beige-mid); border-color: var(--beige-dark); }
        .btn-del:hover   { background: #f0e6e6; border-color: #dfc8c8; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gris-texte);
        }
        .empty-state .icon { font-size: 36px; margin-bottom: 12px; opacity: 0.4; }
        .empty-state p { margin-bottom: 16px; font-style: italic; }

        /* ===== MODALS ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: var(--blanc);
            border-radius: 2px;
            border: 1px solid var(--gris-bordure);
            width: 100%;
            max-width: 640px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gris-bordure);
            background: var(--noir);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-head h2 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.10em;
            color: var(--blanc);
        }

        .modal-close {
            background: none;
            border: none;
            color: rgba(255,255,255,0.6);
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            padding: 0 4px;
            transition: color 0.15s;
        }
        .modal-close:hover { color: var(--blanc); }

        .modal-body { padding: 28px 24px; }

        .modal-head-danger { background: #4a1a1a; }

        /* ===== FORMULAIRE MODAL ===== */
        .section-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.10em;
            color: var(--gris-texte);
            margin-bottom: 14px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--gris-bordure);
        }

        .field-group { margin-bottom: 16px; }

        .field-label {
            display: block;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--gris-texte);
            margin-bottom: 5px;
        }

        .required { color: #8a4a4a; }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gris-bordure);
            border-radius: 2px;
            background: var(--blanc);
            color: var(--noir);
            font-size: 13px;
            font-family: 'Arial', sans-serif;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color 0.15s;
            resize: vertical;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--beige-dark);
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%235a5a5a' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        .row-fields {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .row-fields-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* ===== FOOTER MODAL ===== */
        .modal-foot {
            padding: 16px 24px;
            border-top: 1px solid var(--gris-bordure);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: var(--blanc);
            color: var(--gris-texte);
            border: 1px solid var(--gris-bordure);
            padding: 10px 22px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-cancel:hover { background: var(--beige-mid); color: var(--noir); }

        .btn-save {
            background: var(--noir);
            color: var(--blanc);
            border: none;
            padding: 10px 22px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-save:hover { background: var(--noir-light); }

        .btn-del-confirm {
            background: #4a1a1a;
            color: var(--blanc);
            border: none;
            padding: 10px 22px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-del-confirm:hover { background: #6a2a2a; }

        /* ===== MODAL SUPPRESSION ===== */
        .suppr-body {
            text-align: center;
            padding: 36px 24px 28px;
        }
        .suppr-icon { font-size: 32px; margin-bottom: 14px; opacity: 0.5; }
        .suppr-body p { font-size: 14px; margin-bottom: 6px; }
        .suppr-body .suppr-nom { font-weight: bold; font-size: 15px; }
        .suppr-body small { color: var(--gris-texte); font-style: italic; }
    </style>
</head>
<body>

<div class="page-wrap">

    <!-- EN-TÊTE -->
    <div class="page-header">
        <div>
            <h1>Gestion des formations</h1>
            <span class="sous-titre"><?= count($formations) ?> formation(s) au total</span>
        </div>
        <a href="ajouterF.php" class="btn-primary">+ Nouvelle formation</a>
    </div>

    <!-- MESSAGE -->
    <?php if ($message): ?>
        <div class="msg msg-<?= $type_message === 'success' ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- TABLEAU -->
    <div class="table-wrap">
        <div class="table-header">
            <span>Liste des formations</span>
        </div>

        <?php if (empty($formations)): ?>
            <div class="empty-state">
                <div class="icon">&#128193;</div>
                <p>Aucune formation enregistrée.</p>
                <a href="ajouterF.php" class="btn-primary">+ Ajouter une formation</a>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Durée</th>
                            <th>Formateur</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formations as $f): ?>
                        <tr>
                            <td class="td-muted"><?= (int)$f['id'] ?></td>
                            <td class="td-titre">
                                <div class="titre-nom"><?= htmlspecialchars($f['titre']) ?></div>
                                <?php if (!empty($f['description'])): ?>
                                    <div class="titre-desc"><?= htmlspecialchars(tronquer($f['description'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $f['type'] === 'Presentiel' ? 'badge-presentiel' : 'badge-enligne' ?>">
                                    <?= $f['type'] === 'Presentiel' ? 'Présentiel' : 'En ligne' ?>
                                </span>
                            </td>
                            <td><?= (int)$f['duree'] ?>h</td>
                            <td class="td-muted"><?= (int)$f['id_formateur'] ?></td>
                            <td class="td-muted"><?= date('d/m/Y', strtotime($f['date_debut'])) ?></td>
                            <td class="td-muted"><?= date('d/m/Y', strtotime($f['date_fin'])) ?></td>
                            <td>
                                <div class="btn-actions">
                                    <button class="btn-action btn-edit btn-modifier"
                                            title="Modifier"
                                            data-id="<?= (int)$f['id'] ?>"
                                            data-titre="<?= htmlspecialchars($f['titre'], ENT_QUOTES) ?>"
                                            data-description="<?= htmlspecialchars($f['description'] ?? '', ENT_QUOTES) ?>"
                                            data-prerequis="<?= htmlspecialchars($f['prerequis'] ?? '', ENT_QUOTES) ?>"
                                            data-type="<?= htmlspecialchars($f['type'], ENT_QUOTES) ?>"
                                            data-duree="<?= (int)$f['duree'] ?>"
                                            data-formateur="<?= (int)$f['id_formateur'] ?>"
                                            data-salle="<?= $f['id_salle'] ? (int)$f['id_salle'] : '' ?>"
                                            data-debut="<?= htmlspecialchars($f['date_debut'], ENT_QUOTES) ?>"
                                            data-fin="<?= htmlspecialchars($f['date_fin'], ENT_QUOTES) ?>">
                                        ✏
                                    </button>
                                    <button class="btn-action btn-del btn-supprimer"
                                            title="Supprimer"
                                            data-id="<?= (int)$f['id'] ?>"
                                            data-titre="<?= htmlspecialchars($f['titre'], ENT_QUOTES) ?>">
                                        &#128465;
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL MODIFICATION -->
<div class="modal-overlay" id="modalModif">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Modifier la formation</h2>
            <button class="modal-close" data-close="modalModif">&#10005;</button>
        </div>
        <form method="POST" action="modifierF.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id" id="modif_id">

            <div class="modal-body">
                <div class="section-label">Informations générales</div>

                <div class="field-group">
                    <label class="field-label">Titre <span class="required">*</span></label>
                    <input type="text" name="titre" id="modif_titre" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Description</label>
                    <textarea name="description" id="modif_description" rows="3"></textarea>
                </div>
                <div class="field-group">
                    <label class="field-label">Prérequis</label>
                    <input type="text" name="prerequis" id="modif_prerequis">
                </div>

                <div class="section-label" style="margin-top:24px;">Détails</div>

                <div class="row-fields field-group">
                    <div>
                        <label class="field-label">Type <span class="required">*</span></label>
                        <select name="type" id="modif_type">
                            <option value="Presentiel">Présentiel</option>
                            <option value="enligne">En ligne</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Durée (h) <span class="required">*</span></label>
                        <input type="number" name="duree" id="modif_duree" min="1" required>
                    </div>
                    <div>
                        <label class="field-label">ID Formateur <span class="required">*</span></label>
                        <input type="number" name="id_formateur" id="modif_id_formateur" min="1" required>
                    </div>
                </div>

                <div class="row-fields field-group">
                    <div>
                        <label class="field-label">ID Salle</label>
                        <input type="number" name="id_salle" id="modif_id_salle" min="1">
                    </div>
                    <div>
                        <label class="field-label">Date début <span class="required">*</span></label>
                        <input type="date" name="date_debut" id="modif_date_debut" required>
                    </div>
                    <div>
                        <label class="field-label">Date fin <span class="required">*</span></label>
                        <input type="date" name="date_fin" id="modif_date_fin" required>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-cancel" data-close="modalModif">Annuler</button>
                <button type="submit" class="btn-save">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SUPPRESSION -->
<div class="modal-overlay" id="modalSuppr">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-head modal-head-danger">
            <h2>Confirmer la suppression</h2>
            <button class="modal-close" data-close="modalSuppr">&#10005;</button>
        </div>
        <form method="POST" action="modifierF.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="action" value="supprimer">
            <input type="hidden" name="id" id="suppr_id">

            <div class="suppr-body">
                <div class="suppr-icon">&#9888;</div>
                <p>Voulez-vous vraiment supprimer la formation :</p>
                <p class="suppr-nom" id="suppr_titre_affiche"></p>
                <br>
                <small>Cette action est irréversible.</small>
            </div>

            <div class="modal-foot" style="justify-content:center;">
                <button type="button" class="btn-cancel" data-close="modalSuppr">Annuler</button>
                <button type="submit" class="btn-del-confirm">Oui, supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    document.querySelectorAll('[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function() { closeModal(this.dataset.close); });
    });

    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    /* Boutons MODIFIER */
    document.querySelectorAll('.btn-modifier').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            document.getElementById('modif_id').value           = d.id;
            document.getElementById('modif_titre').value        = d.titre;
            document.getElementById('modif_description').value  = d.description;
            document.getElementById('modif_prerequis').value    = d.prerequis;
            document.getElementById('modif_type').value         = d.type;
            document.getElementById('modif_duree').value        = d.duree;
            document.getElementById('modif_id_formateur').value = d.formateur;
            document.getElementById('modif_id_salle').value     = d.salle;
            document.getElementById('modif_date_debut').value   = d.debut;
            document.getElementById('modif_date_fin').value     = d.fin;
            openModal('modalModif');
        });
    });

    /* Boutons SUPPRIMER */
    document.querySelectorAll('.btn-supprimer').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('suppr_id').value                  = this.dataset.id;
            document.getElementById('suppr_titre_affiche').textContent = this.dataset.titre;
            openModal('modalSuppr');
        });
    });
});
</script>
</body>
</html>