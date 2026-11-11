<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Salles</title>
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
            background-color: var(--beige);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 48px 24px;
            color: var(--noir);
        }

        /* ===== TITRES ===== */
        h2 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--noir);
            margin-bottom: 20px;
            margin-top: 36px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gris-bordure);
            width: 70%;
        }

        h2:first-of-type {
            margin-top: 0;
        }

        h3 {
            font-size: 14px;
            font-weight: bold;
            color: var(--noir);
            margin-bottom: 16px;
            font-style: italic;
        }

        hr {
            width: 70%;
            border: none;
            border-top: 1px solid var(--gris-bordure);
            margin: 36px 0 24px;
        }

        /* ===== MESSAGES ===== */
        .message-succes {
            background-color: var(--beige-mid);
            color: var(--noir);
            border: 1px solid var(--beige-dark);
            padding: 10px 18px;
            border-radius: 2px;
            margin-bottom: 20px;
            text-align: center;
            width: 70%;
            font-size: 13px;
        }

        .message-erreur {
            background-color: #f0e6e6;
            color: #4a1a1a;
            border: 1px solid #dfc8c8;
            padding: 10px 18px;
            border-radius: 2px;
            margin-bottom: 20px;
            text-align: center;
            width: 70%;
            font-size: 13px;
        }

        /* ===== TABLEAU ===== */
        .table-wrap {
            width: 70%;
            background: var(--blanc);
            border: 1px solid var(--gris-bordure);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: var(--beige-mid);
            color: var(--noir);
            font-weight: bold;
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid var(--gris-bordure);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #ece7de;
            color: var(--noir);
            text-align: center;
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }

        tr:hover td {
            background: var(--beige);
            transition: background 0.1s;
        }

        .aucun {
            text-align: center;
            color: var(--gris-texte);
            padding: 32px;
            font-size: 14px;
            font-style: italic;
            width: 70%;
        }

        /* ===== BADGES ===== */
        .badge-oui {
            display: inline-block;
            background: var(--beige-mid);
            color: var(--noir);
            border: 1px solid var(--gris-bordure);
            padding: 3px 12px;
            border-radius: 1px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-non {
            display: inline-block;
            background: #f0e6e6;
            color: #4a1a1a;
            border: 1px solid #dfc8c8;
            padding: 3px 12px;
            border-radius: 1px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-type {
            display: inline-block;
            background: var(--beige);
            color: var(--noir);
            border: 1px solid var(--gris-bordure);
            padding: 3px 12px;
            border-radius: 1px;
            font-size: 11px;
            letter-spacing: 0.03em;
        }

        /* ===== BOUTON MODIFIER (dans tableau) ===== */
        .btn-modifier {
            background: var(--noir);
            color: var(--blanc);
            border: none;
            padding: 7px 16px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            transition: background 0.15s;
        }

        .btn-modifier:hover {
            background: var(--noir-light);
        }

        /* ===== FORMULAIRE ===== */
        .form-modif {
            background: var(--blanc);
            padding: 28px 28px 24px;
            width: 400px;
            border-radius: 2px;
            border: 1px solid var(--gris-bordure);
        }

        .form-modif form + form {
            background: none;
            border: none;
            padding: 0;
            margin-top: 8px;
            box-shadow: none;
        }

        .field-label {
            display: block;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--gris-texte);
            margin-bottom: 5px;
        }

        .field-group {
            margin-bottom: 16px;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gris-bordure);
            border-radius: 2px;
            background: var(--blanc);
            color: var(--noir);
            font-size: 13px;
            font-family: 'Arial', sans-serif;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color 0.15s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: var(--beige-dark);
        }

        input[readonly] {
            background-color: var(--beige-mid);
            color: var(--gris-texte);
            cursor: not-allowed;
        }

        /* Flèche custom pour select */
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%235a5a5a' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        /* ===== BOUTONS FORMULAIRE ===== */
        .btn-valider {
            width: 100%;
            padding: 12px;
            background: var(--noir);
            border: none;
            color: var(--blanc);
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-radius: 2px;
            cursor: pointer;
            transition: background 0.15s;
            margin-top: 4px;
        }

        .btn-valider:hover {
            background: var(--noir-light);
        }

        .btn-annuler {
            width: 400px;
            padding: 11px;
            background: var(--blanc);
            border: 1px solid var(--gris-bordure);
            color: var(--gris-texte);
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-radius: 2px;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.15s, color 0.15s;
        }

        .btn-annuler:hover {
            background: var(--beige-mid);
            color: var(--noir);
        }

        /* Formulaire inline dans le tableau */
        td form {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            width: auto;
            box-shadow: none;
        }
    </style>
</head>
<body>

<?php
$con = mysqli_connect("localhost", "root", "", "centredeformations");
if (!$con) {
    die("<div class='message-erreur'>Erreur de connexion : " . mysqli_connect_error() . "</div>");
}

$message     = "";
$salle_modif = null;

// ── MODIFICATION ──
if (isset($_POST['valider_modif'])) {
    $ref   = $_POST['reference'];
    $nom   = $_POST['nom'];
    $type  = $_POST['type_salle'];
    $capa  = $_POST['capacite'];
    $dispo = $_POST['disponibilite'];

    $sql = "UPDATE salle SET
                nom           = '$nom',
                type_salle    = '$type',
                capacite      = '$capa',
                disponibilite = '$dispo'
            WHERE reference = '$ref'";

    $result = mysqli_query($con, $sql);

    if ($result && mysqli_affected_rows($con) > 0) {
        $message = "<div class='message-succes'>&#10003; Salle <strong>" . htmlspecialchars($ref) . "</strong> modifiée avec succès.</div>";
    } elseif ($result) {
        $message = "<div class='message-erreur'>&#9888; Aucune modification effectuée (données identiques).</div>";
    } else {
        $message = "<div class='message-erreur'>&#10007; Erreur lors de la modification.</div>";
    }
}

// ── SÉLECTION D'UNE SALLE ──
if (isset($_POST['modifier']) && !empty($_POST['reference'])) {
    $ref    = $_POST['reference'];
    $sql    = "SELECT * FROM salle WHERE reference = '$ref'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $salle_modif = mysqli_fetch_assoc($result);
    } else {
        $message = "<div class='message-erreur'>&#10007; Salle introuvable.</div>";
    }
}

// ── LISTE ──
$sql_liste = "SELECT * FROM salle ORDER BY nom ASC";
$liste     = mysqli_query($con, $sql_liste);
?>

<h2>Modifier une salle</h2>

<?= $message ?>

<h2>Liste des salles</h2>

<?php if (!$liste || mysqli_num_rows($liste) == 0): ?>
    <p class="aucun">Aucune salle disponible dans la base de données.</p>
<?php else: ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Référence</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Capacité</th>
                    <th>Disponibilité</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($liste)): ?>
                <?php
                    $ref   = htmlspecialchars($row['reference']);
                    $nom   = htmlspecialchars($row['nom']);
                    $type  = htmlspecialchars($row['type_salle']);
                    $capa  = htmlspecialchars($row['capacite']);
                    $dispo = $row['disponibilite'];

                    $badge_dispo = ($dispo == 'oui')
                        ? "<span class='badge-oui'>Disponible</span>"
                        : "<span class='badge-non'>Indisponible</span>";

                    $badge_type = "<span class='badge-type'>" . ucfirst($type) . "</span>";
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_salle']) ?></td>
                    <td><?= $ref ?></td>
                    <td><?= $nom ?></td>
                    <td><?= $badge_type ?></td>
                    <td><?= $capa ?> places</td>
                    <td><?= $badge_dispo ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="reference" value="<?= $ref ?>">
                            <button type="submit" name="modifier" class="btn-modifier">
                                Modifier
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<!-- ── FORMULAIRE DE MODIFICATION ── -->
<?php if ($salle_modif): ?>

    <hr>
    <h3>Modifier : <?= htmlspecialchars($salle_modif['nom']) ?></h3>

    <div class="form-modif">
        <form method="post">

            <div class="field-group">
                <label class="field-label">Référence</label>
                <input type="text" name="reference"
                       value="<?= htmlspecialchars($salle_modif['reference']) ?>"
                       readonly>
            </div>

            <div class="field-group">
                <label class="field-label">Nom</label>
                <input type="text" name="nom"
                       value="<?= htmlspecialchars($salle_modif['nom']) ?>"
                       required>
            </div>

            <div class="field-group">
                <label class="field-label">Type de salle</label>
                <select name="type_salle">
                    <option value="classique"    <?= $salle_modif['type_salle'] == 'classique'    ? 'selected' : '' ?>>Classique</option>
                    <option value="informatique" <?= $salle_modif['type_salle'] == 'informatique' ? 'selected' : '' ?>>Informatique</option>
                    <option value="conference"   <?= $salle_modif['type_salle'] == 'conference'   ? 'selected' : '' ?>>Conférence</option>
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Capacité (places)</label>
                <input type="number" name="capacite"
                       value="<?= htmlspecialchars($salle_modif['capacite']) ?>"
                       min="1" required>
            </div>

            <div class="field-group">
                <label class="field-label">Disponibilité</label>
                <select name="disponibilite">
                    <option value="oui" <?= $salle_modif['disponibilite'] == 'oui' ? 'selected' : '' ?>>Disponible</option>
                    <option value="non" <?= $salle_modif['disponibilite'] == 'non' ? 'selected' : '' ?>>Indisponible</option>
                </select>
            </div>

            <button type="submit" name="valider_modif" class="btn-valider">
                Enregistrer les modifications
            </button>

        </form>
    </div>

    <form method="get">
        <button type="submit" class="btn-annuler">Annuler</button>
    </form>

<?php endif; ?>

<?php mysqli_close($con); ?>

</body>
</html>