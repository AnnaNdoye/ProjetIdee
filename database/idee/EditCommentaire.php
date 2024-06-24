<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$connexion = new mysqli($host, $user, $password, $database);

if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$id_commentaire = $data['id_commentaire'];
$contenu = $data['contenu'];

$query = "UPDATE commentaire SET contenu = ?, date_modification = NOW() WHERE id_commentaire = ? AND employe_id = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param('sii', $contenu, $id_commentaire, $_SESSION['user_id']);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$connexion->close();
