<?php
session_start();

/* verifier session */
if (!isset($_SESSION['id_user'])) {
    die("Utilisateur non connecté.");
}

/* connexion base */
$con = new mysqli("localhost", "root", "", "centredeformations");

if ($con->connect_error) {
    die("Erreur de connexion : " . $con->connect_error);
}

/* recuperer id user */
$id_user = $_SESSION['id_user'];

/* recuperer inscription */
$stmt = $con->prepare("
    SELECT i.id_inscription
    FROM inscription i
    JOIN compte c ON i.id_compte = c.id_compte
    WHERE c.id_user = ?
    LIMIT 1
");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$inscription = $result->fetch_assoc();

if (!$inscription) {
    die("Aucune inscription trouvée.");
}

$id_inscription = $inscription['id_inscription'];

/* messages */
$message = "";
$erreur = "";

/* formulaire */
if (isset($_POST['valider'])) {

    $type  = trim($_POST['type']);
    $cin   = trim($_POST['cin']);
    $nom   = trim($_POST['nom']);
    $carte = trim($_POST['carte']);
    $cvv   = trim($_POST['cvv']);

    /* validation */
    if (empty($type)) {
        $erreur = "Choisissez un type de carte.";
    } elseif (!ctype_digit($cin) || strlen($cin) != 8) {
        $erreur = "Le CIN doit contenir 8 chiffres.";
    } elseif (empty($nom)) {
        $erreur = "Entrez le nom du titulaire.";
    } elseif (!ctype_digit($carte) || strlen($carte) != 16) {
        $erreur = "Le numéro de carte doit contenir 16 chiffres.";
    } elseif (!ctype_digit($cvv) || strlen($cvv) != 4) {
        $erreur = "Le CVV doit contenir 4 chiffres.";
    } else {

        /* masquer carte */
        $numero_masque = "************" . substr($carte, -4);

        /* insertion paiement */
        $stmt = $con->prepare("
            INSERT INTO paiement (cin, nom_titulaire, numero, code_pin, id_inscription)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssi", $cin, $nom, $numero_masque, $cvv, $id_inscription);

        if ($stmt->execute()) {

            /* update statut */
            $stmt2 = $con->prepare("
                UPDATE inscription SET statut='paye' WHERE id_inscription=?
            ");
            $stmt2->bind_param("i", $id_inscription);
            $stmt2->execute();

            $message = "Paiement réussi ! Votre inscription est confirmée.";
        } else {
            $erreur = "Erreur lors du paiement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Paiement</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f1ea;
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .form-card {
      width: 420px;
      background: #ffffff;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      border: 1px solid #eee;
    }
    h2 { margin-top: 0; font-size: 22px; color: #111; }
    label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 4px; }
    input, select {
      width: 100%; padding: 12px; margin-bottom: 14px;
      border-radius: 8px; border: 1px solid #ddd;
      background: #fafafa; font-size: 14px; box-sizing: border-box;
    }
    input:focus, select:focus { border-color: #000; outline: none; background: #fff; }
    .erreur { background: #fff0f0; border: 1px solid #ffcccc; color: #cc0000; padding: 10px; border-radius: 8px; margin-bottom: 14px; }
    .succes { background: #f0fff4; border: 1px solid #b2f0c5; color: #007700; padding: 12px; border-radius: 8px; text-align: center; }
    .btn-valider {
      background: #111; color: #fff; padding: 12px; width: 100%;
      border: none; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer;
    }
    .btn-valider:hover { background: #333; }
    .btn-retour {
      display: block; background: #e6e6e6; color: #111; padding: 12px;
      border-radius: 8px; font-size: 15px; font-weight: bold;
      margin-top: 10px; text-align: center; text-decoration: none;
    }
    .btn-retour:hover { background: #ccc; }
  </style>
</head>
<body>
<form action="paiement.php" method="post">
  <div class="form-card">

    <h2>Paiement</h2>

    <?php if ($erreur != "") { ?>
      <div class="erreur"><?php echo $erreur; ?></div>
    <?php } ?>

    <?php if ($message != "") { ?>
      <div class="succes"><?php echo $message; ?></div>
      <a href="accueil.php" class="btn-retour">Retour a l'accueil</a>

    <?php } else { ?>

      <label>Type de carte</label>
      <select name="type">
        <option value="">-- Selectionner --</option>
        <option value="nationale">Carte nationale</option>
        <option value="edinar">E-dinar</option>
        <option value="internationale">Carte internationale</option>
      </select>

      <label>CIN</label>
      <input name="cin" placeholder="8 chiffres" maxlength="8">

      <label>Nom du titulaire</label>
      <input name="nom" placeholder="Nom titulaire">

      <label>Numero de carte</label>
      <input name="carte" placeholder="16 chiffres" maxlength="16">

      <label>Code de securite</label>
      <input name="cvv" type="password" placeholder="4 chiffres" maxlength="4">

      <button type="submit" name="valider" class="btn-valider">Valider paiement</button>
      <a href="inscription2.html" class="btn-retour">Retour</a>

    <?php } ?>

  </div>
</form>
</body>
</html>