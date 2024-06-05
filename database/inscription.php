<?php
    // Initialiser les paramètres de connexion à la base de données
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "idee";

    // Récupérer les valeurs des champs du formulaire
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $sexe = $_POST['sexe'];
    $poste = $_POST['poste'];
    $departement = $_POST['departement'];

    //chiffrer le mot de passe
    $hash_mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Établir une connexion à la base de données
    $connection = mysqli_connect($host, $user, $password, $database);

    // Vérifier si la connexion a échoué
    if ($connection->connect_error) 
    {
        die("Erreur de connexion à la base de données : " . $connection->connect_error);
    }
    else 
    {
        // Vérifier si l'email existe déjà
        $everification_email = "SELECT * FROM employe WHERE email = '$email'";
        $resultat_email = mysqli_query($connection, $everification_email);

        if (mysqli_num_rows($resultat_email) > 0) 
        {
            session_start();
            $_SESSION['prenom'] = $prenom;
            $_SESSION['nom'] = $nom;
            $_SESSION['sexe'] = $sexe;
            $_SESSION['poste'] = $poste;
            $_SESSION['departement'] = $departement;
            $_SESSION['erreur_email'] = "Erreur : L'email existe déjà.";
            header("Location: ../html/Inscription.php");
            exit();
        } 
        else 
        {
            // Préparer la requête d'insertion
            $requete1 = "INSERT INTO employe (prenom, nom, email, mot_de_passe, sexe, poste, departement) VALUES ('$prenom', '$nom', '$email', '$hash_mot_de_passe', '$sexe', '$poste', '$departement')";
            $resultat1 = mysqli_query($connection, $requete1);

            // Vérifier si la requête a échoué
            if (!$resultat1) 
            {
                die("Erreur lors de la requête : " . mysqli_error($connection));
            } 
            else 
            {
                echo "Inscription Validée";
                header("Location: ../html/idee/AccueilIdee.html");
            }
        }
    }

    // Fermer la connexion
    mysqli_close($connection);

    
