<?php
$conn = mysqli_connect("localhost", "root", "", "centredeformations");

if (!$conn) {
    die("Connexion échouée : " . mysqli_connect_error());
}

/**
 * Requête compatible avec votre schéma :
 * 1. On rejoint 'utilisateur' pour avoir le Nom/Prénom (car 'participant' n'a que l'ID).
 * 2. On rejoint 'presence' pour compter les absences.
 * 3. On filtre pour ceux ayant strictement plus de 3 absences (soit 4 et plus).
 */
$sql = "SELECT 
            u.id_user, 
            u.nom, 
            u.prenom, 
            COUNT(p.id_presence) AS total_absences
        FROM utilisateur u
        INNER JOIN participant part ON u.id_user = part.id_user
        INNER JOIN presence p ON part.id_user = p.id_participant
        WHERE p.statut = 'absent'
        GROUP BY u.id_user
        HAVING total_absences >= 4"; // Compatible avec votre demande de "4 absences"

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Alertes Absences - Administration</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fffcde; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: center; }
        th { background: #000000; color: white; text-transform: uppercase; font-size: 14px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        h2 { text-align: center; color: #2c3e50; }
        .badge { background: #e74c3c; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>⚠️ Alertes : Participants à risque (4+ absences)</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom & Prénom</th>
                <th>Total Absences</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $nomComplet = htmlspecialchars($row['nom'] . " " . $row['prenom']);
                    echo "<tr>
                            <td>#{$row['id_user']}</td>
                            <td>{$nomComplet}</td>
                            <td><span class='badge'>{$row['total_absences']}</span></td>
                            <td style='color: #e74c3c; font-weight: bold;'>Alerte Critique</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='color: gray; padding: 30px;'>Aucun participant n'a atteint le seuil d'alerte.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>