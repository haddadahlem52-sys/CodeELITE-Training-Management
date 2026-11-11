<?php
$conn = new mysqli("localhost", "root", "", "centredeformations");
 
if ($conn->connect_error) {
    die("Erreur connexion: " . $conn->connect_error);
}
 
// =======================
// ENREGISTREMENT
// =======================
if (isset($_POST['enregistrer'])) {
    $id_session = intval($_POST['id_session']);
    $date = date("Y-m-d");
 
    if (!empty($_POST['statut']) && is_array($_POST['statut'])) {
        foreach ($_POST['statut'] as $id_participant => $statut) {
            $id_participant = intval($id_participant);
            $statut = ($statut === 'present') ? 'present' : 'absent';
 
            $check = $conn->query("SELECT id_presence FROM presence 
                                   WHERE id_participant = $id_participant 
                                   AND id_session = $id_session 
                                   AND date_presence = '$date'");
 
            if ($check->num_rows === 0) {
                $conn->query("INSERT INTO presence (id_participant, id_session, date_presence, statut)
                              VALUES ($id_participant, $id_session, '$date', '$statut')");
            }
 
            if ($statut === 'absent') {
                $sql_abs = "SELECT COUNT(*) as total FROM (
                                SELECT statut FROM presence
                                WHERE id_participant = $id_participant
                                ORDER BY date_presence DESC LIMIT 3
                            ) AS dernieres WHERE statut = 'absent'";
 
                $res = $conn->query($sql_abs);
                $data = $res->fetch_assoc();
 
                if ($data['total'] >= 3) {
                    $res_session = $conn->query("SELECT id_formation FROM sessions WHERE id = $id_session");
                    $session_data = $res_session->fetch_assoc();
                    $id_formation = intval($session_data['id_formation']);
 
                    $check_alerte = $conn->query("SELECT id_alerte FROM alerte_absence 
                                                 WHERE id_user = $id_participant 
                                                 AND id_formation = $id_formation 
                                                 AND DATE(date_alerte) = '$date'");
 
                    if ($check_alerte->num_rows === 0) {
                        $res_nb = $conn->query("SELECT COUNT(*) as nb FROM presence 
                                               WHERE id_participant = $id_participant AND statut = 'absent'");
                        $nb_abs = $res_nb->fetch_assoc()['nb'];
 
                        $conn->query("INSERT INTO alerte_absence (id_user, id_formation, nb_absences, statut, date_alerte)
                                      VALUES ($id_participant, $id_formation, $nb_abs, 'non_lu', NOW())");
 
                        $alerte_msg = "Alerte absence générée pour le participant ID: $id_participant";
                    }
                }
            }
        }
        $success_msg = "Présences enregistrées avec succès.";
    }
}
 
// =======================
// CHARGEMENT DES PARTICIPANTS
// =======================
$id_session_affichage = 5;
 
$query = "SELECT u.id_user AS id_participant, u.nom, u.prenom
          FROM utilisateur u
          INNER JOIN participant p ON u.id_user = p.id_user
          INNER JOIN compte c ON u.id_user = c.id_user
          INNER JOIN inscription i ON c.id_compte = i.id_compte
          WHERE i.id_session = $id_session_affichage
          AND c.statut = 'actif'
          ORDER BY u.nom ASC";
 
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des présences</title>
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
 
        * { box-sizing: border-box; margin: 0; padding: 0; }
 
        body {
            font-family: 'Arial', sans-serif;
            background: var(--beige);
            color: var(--noir);
            min-height: 100vh;
            padding: 48px 32px;
        }
 
        /* ===== EN-TÊTE ===== */
        .page-header {
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gris-bordure);
        }
 
        .page-header h2 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.10em;
            color: var(--noir);
            margin-bottom: 4px;
        }
 
        .page-header p {
            font-size: 13px;
            color: var(--gris-texte);
            font-style: italic;
        }
 
        /* ===== MESSAGES ===== */
        .msg {
            display: inline-block;
            padding: 10px 20px;
            font-size: 13px;
            letter-spacing: 0.03em;
            margin-bottom: 24px;
            border-radius: 2px;
        }
        .msg-success {
            background: var(--beige-mid);
            color: var(--noir);
            border: 1px solid var(--beige-dark);
        }
        .msg-alert {
            background: #f0e6e6;
            color: #4a1a1a;
            border: 1px solid #dfc8c8;
        }
 
        /* ===== TABLE ===== */
        .table-wrap {
            background: var(--blanc);
            border: 1px solid var(--gris-bordure);
            border-radius: 2px;
            overflow: hidden;
            max-width: 680px;
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
            padding: 13px 20px;
            text-align: left;
            border-bottom: 1px solid var(--gris-bordure);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }
 
        th:not(:first-child) {
            text-align: center;
        }
 
        td {
            padding: 13px 20px;
            border-bottom: 1px solid #ece7de;
            color: var(--noir);
            vertical-align: middle;
        }
 
        td:not(:first-child) {
            text-align: center;
        }
 
        tr:last-child td {
            border-bottom: none;
        }
 
        tr:hover td {
            background: var(--beige);
            transition: background 0.1s;
        }
 
        .nom-cell {
            font-size: 14px;
            color: var(--noir);
        }
 
        .empty-row td {
            color: var(--gris-texte);
            font-style: italic;
            text-align: center;
            padding: 24px;
        }
 
        /* ===== RADIO BOUTONS ===== */
        .radio-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
 
        input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--gris-bordure);
            border-radius: 50%;
            background: var(--blanc);
            cursor: pointer;
            position: relative;
            transition: border-color 0.15s;
        }
 
        input[type="radio"]:hover {
            border-color: var(--beige-dark);
        }
 
        /* Présent → noir */
        .radio-present input[type="radio"]:checked {
            background: var(--noir);
            border-color: var(--noir);
        }
 
        /* Absent → beige foncé */
        .radio-absent input[type="radio"]:checked {
            background: var(--beige-dark);
            border-color: var(--beige-dark);
        }
 
        input[type="radio"]:checked::after {
            content: '';
            display: block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--blanc);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
 
        /* ===== LABEL PRÉSENT / ABSENT ===== */
        .label-present {
            font-size: 11px;
            color: var(--gris-texte);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
 
        .label-absent {
            font-size: 11px;
            color: #8a4a4a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
 
        /* ===== BOUTON SUBMIT ===== */
        .btn-submit {
            margin-top: 24px;
            padding: 12px 32px;
            background: var(--noir);
            color: var(--blanc);
            border: none;
            border-radius: 2px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s;
        }
 
        .btn-submit:hover {
            background: var(--noir-light);
        }
    </style>
</head>
<body>
 
<div class="page-header">
    <h2>Feuille de présence</h2>
    <p>Session n° <?= $id_session_affichage ?> — <?= date('d/m/Y') ?></p>
</div>
 
<?php if (!empty($success_msg)): ?>
    <div class="msg msg-success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
 
<?php if (!empty($alerte_msg)): ?>
    <div class="msg msg-alert">&#9888; <?= htmlspecialchars($alerte_msg) ?></div>
<?php endif; ?>
 
<form method="POST">
    <input type="hidden" name="id_session" value="<?= $id_session_affichage ?>">
 
    <div class="table-wrap">
        <table>
            <tr>
                <th>Nom &amp; Prénom</th>
                <th>Présent</th>
                <th>Absent</th>
            </tr>
 
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="nom-cell">
                        <?= htmlspecialchars($row['nom'] . " " . $row['prenom']) ?>
                    </td>
                    <td>
                        <div class="radio-group radio-present">
                            <input type="radio"
                                   name="statut[<?= $row['id_participant'] ?>]"
                                   value="present" required>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group radio-absent">
                            <input type="radio"
                                   name="statut[<?= $row['id_participant'] ?>]"
                                   value="absent">
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="empty-row">
                    <td colspan="3">Aucun participant inscrit à cette session.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
 
    <?php if ($result->num_rows > 0): ?>
        <button type="submit" name="enregistrer" class="btn-submit">
            Enregistrer les présences
        </button>
    <?php endif; ?>
</form>
 
</body>
</html>