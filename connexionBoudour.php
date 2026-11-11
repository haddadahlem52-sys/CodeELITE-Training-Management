<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>
<body>
<?php
    session_start(); // ✅ AJOUT — obligatoire pour utiliser $_SESSION

    // Connexion à la base de données
    $con = mysqli_connect('localhost', 'root', '', 'centredeformations');

    if (!$con) {
        die("Échec de connexion : " . mysqli_connect_error());
    }

    // Récupération des données POST
    $email = isset($_POST['email'])     ? trim($_POST['email'])     : '';
    $mot   = isset($_POST['password2']) ? trim($_POST['password2']) : '';

    // Vérification que les champs ne sont pas vides
    if (empty($email) || empty($mot)) {
        echo "<script>alert('Veuillez remplir tous les champs ⚠️'); window.history.back();</script>";
        exit;
    }

    // Étape 1 : Vérifier l'utilisateur dans la table utilisateur
    $stmt = mysqli_prepare($con, "SELECT * FROM utilisateur WHERE email = ? AND mot_de_passe = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $mot);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) == 0) {
        echo "<script>alert('Email ou mot de passe incorrect ❌'); window.history.back();</script>";
        exit;
    }

    $user    = mysqli_fetch_assoc($res);
    $id_user = $user['id_user'];
    $role    = $user['role'];

    mysqli_stmt_close($stmt);

    // Étape 2 : Vérification selon le rôle
    if ($role === 'admin') {

        // ✅ AJOUT — session admin
        $_SESSION['id_user'] = $id_user;
        $_SESSION['role']    = 'admin';
        $_SESSION['email']   = $email;

        header("Location: admin.html");
        exit;

    } elseif ($role === 'formateur') {

        $stmt2 = mysqli_prepare($con, "SELECT etat FROM formateur WHERE id_formateur = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id_user);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);

        if (mysqli_num_rows($res2) == 0) {
            echo "<script>alert('Compte formateur introuvable ❌'); window.history.back();</script>";
            exit;
        }

        $formateur = mysqli_fetch_assoc($res2);
        $etat      = $formateur['etat'];
        mysqli_stmt_close($stmt2);

        if ($etat == 1) {
            echo "<script>alert('Votre compte est bloqué 🚫. Veuillez contacter l\\'administrateur.'); window.history.back();</script>";
            exit;
        }

        // ✅ AJOUT — session formateur
        $_SESSION['id_user'] = $id_user;
        $_SESSION['role']    = 'formateur';
        $_SESSION['email']   = $email;

        header("Location: historique_formateur.php"); // ✅ redirige vers historique formateur
        exit;

    } else {
        // Participant
        $stmt3 = mysqli_prepare($con, "SELECT id_compte, statut FROM compte WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt3, "i", $id_user);
        mysqli_stmt_execute($stmt3);
        $res3 = mysqli_stmt_get_result($stmt3);

        if (mysqli_num_rows($res3) == 0) {
            echo "<script>alert('Compte participant introuvable ❌'); window.history.back();</script>";
            exit;
        }

        $compte    = mysqli_fetch_assoc($res3);
        $statut    = $compte['statut'];
        $id_compte = $compte['id_compte']; // ✅ AJOUT — récupérer id_compte
        mysqli_stmt_close($stmt3);

        if ($statut === 'desactive') {
            echo "<script>alert('Votre compte est désactivé 🚫. Veuillez contacter l\\'administrateur.'); window.history.back();</script>";
            exit;
        }

        // ✅ AJOUT — session participant
        $_SESSION['id_user']   = $id_user;
        $_SESSION['id_compte'] = $id_compte;
        $_SESSION['role']      = 'participant';
        $_SESSION['email']     = $email;

        header("Location: historique_participant.php"); // ✅ redirige vers historique participant
        exit;
    }

    mysqli_close($con);
?>
</body>
</html>