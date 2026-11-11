<?php
$conn = new mysqli("localhost", "root", "", "centredeformations");
if ($conn->connect_error) {
    die("Erreur connexion");
}

$sql_formateurs = "SELECT utilisateur.id_user, utilisateur.nom, utilisateur.prenom
                   FROM utilisateur
                   JOIN formateur ON utilisateur.id_user = formateur.id_formateur";
$result_formateurs = $conn->query($sql_formateurs);

$sql_salles = "SELECT id_salle, nom FROM salle";
$result_salles = $conn->query($sql_salles);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Session</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f0e8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem 1rem; /* évite le débordement sur petits écrans */
        }

        /* ✅ h2 et form regroupés dans un conteneur commun */
        .form-card {
            background: #ffffff;
            padding: 36px;
            border-radius: 12px;
            border: 1px solid #e0d8cc;
            width: 400px;
            max-width: 100%;
        }

        h2 {
            text-align: center;
            color: #5a4a3a;
            font-size: 20px;
            margin-bottom: 24px;
            font-weight: 600;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #7a6a5a;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            width: 100%;
            padding: 10px 14px;
            margin-bottom: 18px; /* ✅ l'espacement est géré ici, les <br> sont supprimés */
            border: 1px solid #ddd5c8;
            border-radius: 8px;
            font-size: 14px;
            background: #fdfbf8;
            color: #3a3028;
            outline: none;
            transition: border-color 0.2s;
            appearance: none; /* ✅ ajouté pour uniformiser les select */
        }

        input:focus, select:focus {
            border-color: #b5a090;
            background: #fff;
        }

        button {
            width: 100%;
            padding: 11px;
            background: #42311c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #7a6245;
        }
    </style>
</head>
<body>

<!-- ✅ h2 et form enveloppés dans .form-card -->
<div class="form-card">
    <h2>Ajouter une nouvelle session</h2>

    <form action="traitement_session.php" method="POST">

        <label>Titre :</label>
        <input type="text" name="titre" required>

        <label>Date :</label>
        <input type="date" name="date_session" required>

        <label>Durée :</label>
        <input type="text" name="duree" required>

        <label>Formateur :</label>
        <select name="id_formateur" required>
            <option value="">-- Choisir un formateur --</option>
            <?php
            if ($result_formateurs->num_rows > 0) {
                while ($row = $result_formateurs->fetch_assoc()) {
                    echo '<option value="' . $row["id_user"] . '">'
                         . htmlspecialchars($row["nom"]) . ' ' . htmlspecialchars($row["prenom"])
                         . '</option>';
                }
            }
            ?>
        </select>

        <label>Salle :</label>
        <select name="id_salle" required>
            <option value="">-- Choisir une salle --</option>
            <?php
            if ($result_salles->num_rows > 0) {
                while ($row = $result_salles->fetch_assoc()) {
                    echo '<option value="' . $row["id_salle"] . '">'
                         . htmlspecialchars($row["nom"])
                         . '</option>';
                }
            }
            ?>
        </select>

        <button type="submit">Ajouter</button>
    </form>
</div>

</body>
</html>