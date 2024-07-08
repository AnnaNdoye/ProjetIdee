<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Connexion.php");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idee_id = $_POST['id'];
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $visibilite = $_POST['visibilite'] === 'publique' ? 1 : 0;
    $categorie_id = $_POST['categorie_id'];
    $fichier_nom = '';
    $fichier_type = '';
    $fichier_contenu = '';

    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == UPLOAD_ERR_OK) {
        $fichier_nom = $_FILES['fichier']['name'];
        $fichier_type = $_FILES['fichier']['type'];
        $fichier_contenu = file_get_contents($_FILES['fichier']['tmp_name']);
    }

    $query = "
        UPDATE idee
        SET titre = ?, contenu_idee = ?, est_publique = ?, categorie_id = ?
        WHERE id_idee = ? AND employe_id = ?
    ";

    $stmt = $connexion->prepare($query);
    if ($stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }

    $stmt->bind_param('ssiiii', $titre, $contenu, $visibilite, $categorie_id, $idee_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $stmt->error);
    }

    if ($fichier_nom) {
        $query = "
            INSERT INTO fichier (idee_id, nom_fichier, type, contenu_fichier)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nom_fichier = VALUES(nom_fichier), type = VALUES(type), contenu_fichier = VALUES(contenu_fichier)
        ";
        $stmt = $connexion->prepare($query);
        if ($stmt === false) {
            die("Erreur lors de la préparation de la requête: " . $connexion->error);
        }
        $stmt->bind_param('isss', $idee_id, $fichier_nom, $fichier_type, $fichier_contenu);
        if (!$stmt->execute()) {
            die("Erreur lors de l'exécution de la requête: " . $stmt->error);
        }
    }

    header("Location: ../../html/idee/AccueilIdee.php");
    exit();
}

$connexion->close();