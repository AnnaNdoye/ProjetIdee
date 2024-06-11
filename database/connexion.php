<?php
session_start();

// Initialiser les paramètres de connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Établir une connexion à la base de données
$connection = new mysqli($host, $user, $password, $database);

// Vérifier si la connexion a échoué
if ($connection->connect_error) 
{
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
}

// Vérifier si les données POST sont définies
if (isset($_POST['email']) && isset($_POST['mot_de_passe'])) 
{
    // Récupérer les valeurs des champs du formulaire en les échappant pour prévenir les injections SQL
    $email = $connection->real_escape_string($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Préparer la requête pour vérifier l'e-mail et récupérer l'ID, le mot de passe haché et is_admin
    $stmt = $connection->prepare("SELECT id_employe, mot_de_passe, is_admin FROM employe WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); // Nouvelle manière plus sécurisée de pouvoir exécuter et stocker les résultats

    if ($stmt->num_rows > 0) 
    {
        // L'e-mail existe, récupérer l'ID, le mot de passe haché et is_admin
        $stmt->bind_result($user_id, $hashed_password, $is_admin);
        $stmt->fetch();

        // Vérifier le mot de passe
        if (password_verify($mot_de_passe, $hashed_password)) 
        {
            // Mot de passe correct, stocker l'ID de l'utilisateur dans la session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['mot_de_passe'] = $mot_de_passe;
            $_SESSION['email'] = $email;

            // Rediriger en fonction du statut admin
            if ($is_admin == 1) 
            {
                header("Location: ../html/admin/AccueilAdmin.php");
            } 
            else 
            {
                header("Location: ../html/idee/AccueilIdee.php");
            }
            exit();
        } 
        else 
        {
            // Mot de passe incorrect
            $_SESSION['error_message'] = "Mot de passe incorrect.";
            $_SESSION['email'] = $email;
            header("Location: ../html/connexion.php");
            exit();
        }
    } 
    else 
    {
        // E-mail incorrect
        $_SESSION['error_message'] = "E-mail incorrect.";
        header("Location: ../html/connexion.php");
    }

    // Fermer la connexion
    $stmt->close();
} 
else 
{
    $_SESSION['error_message'] = "Veuillez remplir tous les champs.";
    header("Location: ../html/connexion.php");
    exit();
}

$connection->close();