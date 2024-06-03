<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$email = $_POST['email'];

$connection = mysqli_connect($host, $user, $password, $database);

if ($connection->connect_error) 
{
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
}

$stmt = $connection->prepare("SELECT * FROM employe WHERE prenom = ? AND nom = ? AND email = ?");
$stmt->bind_param("sss", $prenom, $nom, $email);
$stmt->execute();
$result = $stmt->get_result();

session_start();
if ($result->num_rows > 0) 
{
    $_SESSION['email'] = $email; // Stocker l'email dans la session pour la prochaine étape
    header("Location: ../html/ChangePassword.html");
} 
else 
{
    $_SESSION['error_message'] = "Informations incorrectes.";
    header("Location: ../html/PasswordOublie.php");
}

$stmt->close();
mysqli_close($connection);
