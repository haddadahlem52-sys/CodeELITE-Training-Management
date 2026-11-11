<?php
// Connexion à la base
$conn = new mysqli("localhost", "root", "", "centredeformations");

// Vérifier connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// 🔴 SUPPRESSION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $sql_delete = "DELETE FROM salle WHERE id_salle = $id";
    if ($conn->query($sql_delete)) {
        echo "<p style='color:green;'>Salle supprimée avec succès</p>";
    } else {
        echo "<p style='color:red;'>Erreur lors de la suppression</p>";
    }
}

// 🔵 AFFICHAGE
$sql = "SELECT * FROM salle";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des salles</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f6f7fb;
    margin: 0;
    padding: 0;
}

/* HEADER */
.header {
    background: #1e1e2f;
    color: white;
    padding: 15px 30px;
    font-weight: bold;
    font-size: 20px;
}

/* TITRE */
h2 {
    text-align: center;
    margin-top: 30px;
    font-size: 32px;
    color: #2c2c2c;
}

/* CONTAINER */
.container {
    width: 85%;
    margin: 30px auto;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

th {
    background: #f1f3f6;
    color: #555;
    text-transform: uppercase;
    font-size: 13px;
    padding: 14px;
}

td {
    padding: 14px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

/* HOVER */
tr:hover {
    background: #f9fafc;
}

/* BADGE CAPACITE */
.badge {
    background: #e3f2fd;
    color: #2196f3;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 13px;
}

/* BOUTON SUPPRIMER */
.delete-btn {
    background: white;
    border: 1px solid #e74c3c;
    color: #e74c3c;
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 13px;
    transition: 0.3s;
}

.delete-btn:hover {
    background: #e74c3c;
    color: white;
}

/* MESSAGE */
.success {
    text-align: center;
    color: green;
}

.error {
    text-align: center;
    color: red;
}
</style>

</head>
<body>
    
<!-- ------------------>
 <div class="header">CentreFormations</div>

<h2>Liste des Salles</h2>

<div class="container">


<table>
    <tr>
        <th>ID</th>
        <th>Nom Salle</th>
        <th>Capacité</th>
        <th>Action</th>
    </tr>

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row['id_salle']."</td>
                <td>".$row['nom']."</td>
                <td><span class='badge'>".$row['capacite']." places</span></td>
                <td>
                    <a class='delete-btn' href='?delete=".$row['id_salle']."' 
                       onclick='return confirm(\"Voulez-vous vraiment supprimer ?\")'>
                       Supprimer
                    </a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>Aucune salle trouvée</td></tr>";
}
?>

</table>
</div>
</body>
</html>