<?php

// Initialiser les paramètres de connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

//l'utilisateur simple n'est pas un admin
$is_admin = 0;

// Récupérer les valeurs des champs du formulaire
$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$email = $_POST['email'];
$mot_de_passe = $_POST['mot_de_passe'];
$sexe = $_POST['sexe'];
$poste = $_POST['poste'];
$departement_id = $_POST['departement_id'];

// Chiffrer le mot de passe
$hash_mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Établir une connexion à la base de données
$connection = mysqli_connect($host, $user, $password, $database);

// Vérifier si la connexion a échoué
if ($connection->connect_error) {
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
} 
else  //la connexion a réussi
{
    // Vérifier si l'email existe déjà
    $everification_email = "SELECT * FROM employe WHERE email = '$email'";
    $resultat_email = mysqli_query($connection, $everification_email);

    if (mysqli_num_rows($resultat_email) > 0) // Si l'email existe déjà
    {
        session_start();
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['sexe'] = $sexe;
        $_SESSION['poste'] = $poste;
        $_SESSION['departement'] = $departement_id;
        $_SESSION['erreur_email'] = "Erreur : L'email existe déjà.";
        header("Location: ../html/Inscription.php");
        exit();
    } 
    else // Si l'email n'existe pas encore
    {
        //filtrer le mot de passe pour s'assurer que c'est un vrai mot de passe 
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
        {
             // Copier unknown.png vers le répertoire ../html/idee/upload
            $source_photo = "../html/idee/uploads/unknow.png";
            $destination_photo = "uploads/unknow.png"; // Utilisation de l'email comme nom de fichier unique
            copy($source_photo, $destination_photo);

             // Préparer la requête d'insertion
            $requete1 = "INSERT INTO employe (is_admin, prenom, nom, email, mot_de_passe, sexe, poste, departement_id, photo_profil) VALUES ('$is_admin', '$prenom', '$nom', '$email', '$hash_mot_de_passe', '$sexe', '$poste', '$departement_id', '$destination_photo')";
            $resultat1 = mysqli_query($connection, $requete1);

            // Vérifier si la requête a échoué
            if (!$resultat1) 
            {
                die("Erreur lors de la requête : " . mysqli_error($connection));
            } 
            else 
            {
                header("Location: ../html/Connexion.php");
            }
        }

        else  // Si l'email est invalide
        {
            session_start();
            $_SESSION['prenom'] = $prenom;
            $_SESSION['nom'] = $nom;
            $_SESSION['sexe'] = $sexe;
            $_SESSION['poste'] = $poste;
            $_SESSION['departement'] = $departement_id;
            $_SESSION['erreur_email'] = "Erreur : L'email est invalide.Veuillez renseigner un email valide";
            header("Location: ../html/Inscription.php");
            exit();
        }
    }

    // Fermer la connexion à la base de données
    mysqli_close($connection);
}
?>
