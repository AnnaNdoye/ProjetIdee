<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

// Vérifier si l'identifiant de l'idée est fourni
if (!isset($_GET['id'])) {
    header("Location: MesIdees.php?error=missing_id");
    exit();
}

// Informations de connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Connexion à la base de données
$connexion = new mysqli($host, $user, $password, $database);

// Vérifier la connexion à la base de données
if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

// Récupérer l'identifiant de l'idée et de l'utilisateur
$id_idee = intval($_GET['id']);
$employe_id = $_SESSION['user_id'];

// Préparer la requête de suppression
$query = "DELETE FROM idee WHERE id_idee = ? AND employe_id = ?";
$stmt = $connexion->prepare($query);

if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

// Lier les paramètres et exécuter la requête
$stmt->bind_param('ii', $id_idee, $employe_id);

if ($stmt->execute()) {
    header("Location: MesIdees.php?success=idea_deleted");
} else {
    echo "Erreur lors de la suppression: " . $stmt->error;
}

// Fermer la requête et la connexion
$stmt->close();
$connexion->close();