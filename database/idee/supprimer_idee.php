<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: MesIdees.php");
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

$id_idee = intval($_GET['id']);
$employe_id = $_SESSION['user_id'];

$query = "DELETE FROM idee WHERE id_idee = ? AND employe_id = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param('ii', $id_idee, $employe_id);

if ($stmt->execute()) {
    header("Location: MesIdees.php");
} else {
    echo "Erreur lors de la suppression: " . $stmt->error;
}

$stmt->close();
$connexion->close();
