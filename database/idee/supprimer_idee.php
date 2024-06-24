<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../connexion.php");
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

$idee_id = $_GET['id'];

// Supprimer d'abord les fichiers associés à cette idée
$delete_fichiers_query = "DELETE FROM fichier WHERE idee_id = ?";
$stmt_fichiers = $connexion->prepare($delete_fichiers_query);
if ($stmt_fichiers === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}
$stmt_fichiers->bind_param('i', $idee_id);
if (!$stmt_fichiers->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt_fichiers->error);
}
$stmt_fichiers->close();

// Ensuite, supprimer l'idée
$delete_idee_query = "DELETE FROM idee WHERE id_idee = ?";
$stmt_idee = $connexion->prepare($delete_idee_query);
if ($stmt_idee === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}
$stmt_idee->bind_param('i', $idee_id);
if (!$stmt_idee->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt_idee->error);
}
$stmt_idee->close();

header("Location: ../../html/idee/AccueilIdee.php");

$connexion->close();