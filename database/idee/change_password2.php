<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../html/idee/Profil.php");
    exit();
}

// Initialiser les paramètres de connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Récupérer les valeurs des champs du formulaire
$email = $_SESSION['email'];
$mot_de_passe = $_POST['mot_de_passe'];

// Chiffrer le mot de passe
$hashed_password = password_hash($mot_de_passe, PASSWORD_BCRYPT);

// Établir une connexion à la base de données
$connection = new mysqli($host, $user, $password, $database);

// Vérifier si la connexion a échoué
if ($connection->connect_error) {
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
}

// Utiliser des requêtes préparées pour éviter les injections SQL
$stmt = $connection->prepare("UPDATE employe SET mot_de_passe = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    // Mise à jour de la variable de session
    $_SESSION['mot_de_passe'] = $mot_de_passe;
    header("Location: ../../html/idee/Profil.php");
} else {
    echo "Erreur lors de la modification du mot de passe.";
    header("Location: ../../html/idee/Profil.php");
}

// Fermer la connexion
$stmt->close();
$connection->close();
?>
