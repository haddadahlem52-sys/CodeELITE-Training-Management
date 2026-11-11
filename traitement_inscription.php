<?php

$conn = mysqli_connect("localhost", "root", "", "centredeformations");

if (!$conn) {
    die("Erreur connexion");
}

if($_SERVER["REQUEST_METHOD"] == "POST") {

    // 🔹 récupération formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $tel = $_POST['telephone'];

    // 🔹 sécurisation simple
    $nom = mysqli_real_escape_string($conn, $nom);
    $prenom = mysqli_real_escape_string($conn, $prenom);
    $email = mysqli_real_escape_string($conn, $email);
    $tel = mysqli_real_escape_string($conn, $tel);

    $mot_de_passe = password_hash("123456", PASSWORD_DEFAULT);
    $role = "user";

    // 🔴 CHECK EMAIL EXISTE
    $check = "SELECT * FROM utilisateur WHERE email='$email'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {

        // ❌ ALERT si email existe déjà
        echo "<script>
                alert('❌ Cet email est déjà utilisé !');
                window.history.back();
              </script>";
        exit();
    }

    // 1️⃣ INSERT utilisateur
    $sql_user = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, tel, role)
                 VALUES ('$nom', '$prenom', '$email', '$mot_de_passe', '$tel', '$role')";

    if (mysqli_query($conn, $sql_user)) {

        $id_user = mysqli_insert_id($conn);

        // 2️⃣ INSERT compte
        $statut_compte = "actif";

        $sql_compte = "INSERT INTO compte (statut, id_user)
                       VALUES ('$statut_compte', '$id_user')";

        if (mysqli_query($conn, $sql_compte)) {

            $id_compte = mysqli_insert_id($conn);

            // 3️⃣ INSERT inscription
            $id_session = $_POST['session'];
            $date_inscription = date("Y-m-d");
            $statut_inscription = "en attente";

            $sql_inscription = "INSERT INTO inscription (id_compte, id_session, date_inscription, statut)
                                VALUES ('$id_compte', '$id_session', '$date_inscription', '$statut_inscription')";

            if (mysqli_query($conn, $sql_inscription)) {

                // ✔ redirection vers paiement
                header("Location: paiement.html");
                exit();

            } else {
                echo "Erreur inscription : " . mysqli_error($conn);
            }

        } else {
            echo "Erreur compte : " . mysqli_error($conn);
        }

    } else {
        echo "Erreur utilisateur : " . mysqli_error($conn);
    }
}

?>