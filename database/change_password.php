<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../html/password_oublie.html");
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
$connection = mysqli_connect($host, $user, $password, $database);

// Vérifier si la connexion a échoué
if ($connection->connect_error) {
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
}

// Utiliser des requêtes préparées pour éviter les injections SQL
$stmt = $connection->prepare("UPDATE employe SET mot_de_passe = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);
$update_success = $stmt->execute();

if ($update_success) {
    header("Location: ../html/Connexion.php");
} else {
    echo "Erreur lors de la modification du mot de passe.";
    header("Location: ../html/PasswordOublie.php");
}

// Fermer la connexion
$stmt->close();
mysqli_close($connection);
