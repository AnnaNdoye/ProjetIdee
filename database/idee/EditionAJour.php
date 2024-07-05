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

date_default_timezone_set('Africa/Dakar');
$dateCourante = date('Y-m-d H:i:s');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idee_id = $_POST['id'];
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $visibilite = $_POST['visibilite'] == 'publique' ? 1 : 0;
    $categorie_id = $_POST['categorie_id'];
    $employe_id = $_SESSION['user_id'];

    $update_query = "UPDATE idee SET titre = ?, contenu_idee = ?, est_publique = ?, categorie_id = ?, date_modification = ? WHERE id_idee = ? AND employe_id = ?";
    $stmt = $connexion->prepare($update_query);
    if ($stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $stmt->bind_param('ssissii', $titre, $contenu, $visibilite, $categorie_id, $dateCourante, $idee_id, $employe_id);

    if ($stmt->execute()) {
        // Gérer le fichier
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['fichier'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileType = $file['type'];
            $fichierTaille = $file['size'];

            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filePath = $upload_dir . basename($fileName);

            if (move_uploaded_file($fileTmpName, $filePath)) {
                // Vérifier s'il y a déjà un fichier pour cette idée
                $file_check_query = "SELECT id_fichier FROM fichier WHERE idee_id = ?";
                $file_check_stmt = $connexion->prepare($file_check_query);
                $file_check_stmt->bind_param('i', $idee_id);
                $file_check_stmt->execute();
                $file_check_stmt->store_result();

                if ($file_check_stmt->num_rows > 0) {
                    // Mise à jour du fichier existant
                    $file_check_stmt->bind_result($id_fichier);
                    $file_check_stmt->fetch();
                    $file_query = "UPDATE fichier SET nom_fichier = ?, type = ?, taille = ?, contenu_fichier = ? WHERE id_fichier = ?";
                    $file_stmt = $connexion->prepare($file_query);
                    if ($file_stmt === false) {
                        die("Erreur lors de la préparation de la requête de fichier: " . $connexion->error);
                    }
                    $file_stmt->bind_param('ssisi', $fileName, $fileType, $fichierTaille, $filePath, $id_fichier);
                } else {
                    // Insertion d'un nouveau fichier
                    $file_query = "INSERT INTO fichier (nom_fichier, type, taille, contenu_fichier, idee_id) VALUES (?, ?, ?, ?, ?)";
                    $file_stmt = $connexion->prepare($file_query);
                    if ($file_stmt === false) {
                        die("Erreur lors de la préparation de la requête de fichier: " . $connexion->error);
                    }
                    $file_stmt->bind_param('ssisi', $fileName, $fileType, $fichierTaille, $filePath, $idee_id);
                }

                if (!$file_stmt->execute()) {
                    die("Erreur lors de l'insertion ou de la mise à jour du fichier: " . $file_stmt->error);
                }
                $file_stmt->close();
                $file_check_stmt->close();
            } else {
                die("Erreur lors du téléchargement du fichier.");
            }
        }

        $_SESSION['message'] = "Idée modifiée avec succès.";
        header("Location: ../../html/idee/AccueilIdee.php");
        exit();
    } else {
        die("Erreur lors de la mise à jour de l'idée: " . $stmt->error);
    }

}

$connexion->close();