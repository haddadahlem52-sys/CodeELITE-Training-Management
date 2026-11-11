<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Participants</title>
    <style>
        /* Style Global */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fdffea;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
        }

        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Style du Tableau */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 800px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #121415;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Badge pour le statut */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            background-color: #e0e0e0;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="table-container">
    <?php
    try {
        $db = new PDO('mysql:host=localhost;dbname=centredeformations', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $id_session = 5; 

        $query = "SELECT u.nom, u.prenom, u.email, i.statut 
                  FROM utilisateur u
                  JOIN compte c ON u.id_user = c.id_user
                  JOIN inscription i ON c.id_compte = i.id_compte
                  WHERE i.id_session = :id";

        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $id_session]);
        $participants = $stmt->fetchAll();

        echo "<h2>Liste des participants</h2>";
        
        if (count($participants) > 0) {
            echo "<table>";
            echo "<thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Statut</th>
                    </tr>
                  </thead>";
            echo "<tbody>";
            foreach ($participants as $p) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars(strtoupper($p['nom'])) . " " . htmlspecialchars($p['prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($p['email']) . "</td>";
                echo "<td><span class='status-badge'>" . htmlspecialchars($p['statut']) . "</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>Aucun participant inscrit pour cette session.</p>";
        }

    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
    ?>
</div>

</body>
</html>