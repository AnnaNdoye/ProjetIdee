<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$email = $_POST['email'];

$connection = mysqli_connect($host, $user, $password, $database);

if ($connection->connect_error) 
{
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
}

$stmt = $connection->prepare("SELECT * FROM employe WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

session_start();
if ($result->num_rows > 0) 
{
    header("Location: ../html/ChangePassword.html");
    $_SESSION['email'] = $email; // Stocker l'email dans la session pour la prochaine étape
} 
else 
{
    $_SESSION['error_message'] = "Email incorrect.";
    header("Location: ../html/PasswordOublie.php");
}

$stmt->close();
mysqli_close($connection);
