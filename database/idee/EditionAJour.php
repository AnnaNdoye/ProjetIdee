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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idee_id = $_POST['id'];
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $visibilite = $_POST['visibilite'] == 'publique' ? 1 : 0;
    $categorie_id = $_POST['categorie_id'];
    $employe_id = $_SESSION['user_id'];

    $update_query = "UPDATE idee SET titre = ?, contenu_idee = ?, est_publique = ?, categorie_id = ?, date_modification = NOW() WHERE id_idee = ? AND employe_id = ?";
    $stmt = $connexion->prepare($update_query);
    if ($stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $stmt->bind_param('ssiiii', $titre, $contenu, $visibilite, $categorie_id, $idee_id, $employe_id);

    if ($stmt->execute()) {
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['fichier'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileType = $file['type'];

            $upload_dir = 'uploads/';

            $filePath = $upload_dir . basename($fileName);
            if (move_uploaded_file($fileTmpName, $filePath)) {
                $file_query = "INSERT INTO fichier (nom_fichier, chemin_fichier, type_fichier, id_idee) VALUES (?, ?, ?, ?)";
                $file_stmt = $connexion->prepare($file_query);
                if ($file_stmt === false) {
                    die("Erreur lors de la préparation de la requête de fichier: " . $connexion->error);
                }
                $file_stmt->bind_param('sssi', $fileName, $filePath, $fileType, $idee_id);
                if (!$file_stmt->execute()) 
                {
                    die("Erreur lors de l'insertion du fichier: " . $file_stmt->error);
                }
                $file_stmt->close();
            } 
            else 
            {
                die("Erreur lors du téléchargement du fichier.");
            }
        }
        $_SESSION['message'] = "Idée modifiée avec succès.";
        header("Location: ../../html/idee/AccueilIdee.php");
    } else {
        die("Erreur lors de la mise à jour de l'idée: " . $stmt->error);
    }

    $stmt->close();
}

$connexion->close();
