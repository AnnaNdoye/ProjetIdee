<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../html/Connexion.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Créer une connexion
$connexion = new mysqli($host, $user, $password, $database);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idee_id = $_POST['idee_id'];
    $user_id = $_SESSION['user_id'];

    // Vérifier si l'utilisateur a déjà liké cette idée
    $checkQuery = "SELECT * FROM LikeIdee WHERE idee_id = ? AND employe_id = ?";
    $stmt = $connexion->prepare($checkQuery);
    $stmt->bind_param("ii", $idee_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // L'utilisateur a déjà liké cette idée, donc on enlève le like
        $deleteQuery = "DELETE FROM LikeIdee WHERE idee_id = ? AND employe_id = ?";
        $stmt = $connexion->prepare($deleteQuery);
        $stmt->bind_param("ii", $idee_id, $user_id);
        $stmt->execute();
    } else {
        // L'utilisateur n'a pas encore liké cette idée, donc on ajoute le like
        $insertQuery = "INSERT INTO LikeIdee (idee_id, employe_id) VALUES (?, ?)";
        $stmt = $connexion->prepare($insertQuery);
        $stmt->bind_param("ii", $idee_id, $user_id);
        $stmt->execute();
    }

    $stmt->close();
}

$connexion->close();
header("Location: ../../html/idee/IdeePublique.php");
exit();