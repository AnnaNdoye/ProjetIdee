<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$connexion = new mysqli($host, $user, $password, $database);

if ($connexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$idee_id = $data['idee_id'];
$employe_id = $_SESSION['user_id'];

// Vérifier si l'utilisateur a déjà liké l'idée
$query_check = "SELECT * FROM LikeIdee WHERE employe_id = ? AND idee_id = ?";
$stmt_check = $connexion->prepare($query_check);
$stmt_check->bind_param('ii', $employe_id, $idee_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Supprimer le like
    $query_delete = "DELETE FROM LikeIdee WHERE employe_id = ? AND idee_id = ?";
    $stmt_delete = $connexion->prepare($query_delete);
    $stmt_delete->bind_param('ii', $employe_id, $idee_id);
    $success = $stmt_delete->execute();
    echo json_encode(['success' => $success, 'action' => 'unliked']);
} else {
    // Ajouter le like
    $query_insert = "INSERT INTO LikeIdee (employe_id, idee_id) VALUES (?, ?)";
    $stmt_insert = $connexion->prepare($query_insert);
    $stmt_insert->bind_param('ii', $employe_id, $idee_id);
    $success = $stmt_insert->execute();
    echo json_encode(['success' => $success, 'action' => 'liked']);
}

$stmt_check->close();
$stmt_delete->close();
$stmt_insert->close();
$connexion->close();
