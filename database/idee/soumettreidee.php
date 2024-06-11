<?php

session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Créer une connexion
$connexion = mysqli_connect($host, $user, $password, $database);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

// Je récupère les valeurs du formulaire avec POST
$titre = $_POST['titre'];
$contenu = $_POST['contenu'];
$categorie_id = $_POST['categorie_id'];
$visibilite = $_POST['visibilite'] === 'publique' ? 1 : 0; // 1 pour publique, 0 pour privé
$statut = "Soumis";

// Définir le fuseau horaire
date_default_timezone_set('Africa/Dakar');
$dateCourante = date('Y-m-d H:i:s');

// Récupérer l'ID de l'utilisateur
$employe_id = $_SESSION['user_id'];

// Insérer l'idée dans la base de données
$requete1 = "INSERT INTO idee (titre, contenu_idee, est_publique, date_creation, date_modification, employe_id, categorie_id, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($connexion, $requete1);
mysqli_stmt_bind_param($stmt, "ssissiis", $titre, $contenu, $visibilite, $dateCourante, $dateCourante, $employe_id, $categorie_id, $statut);

if (mysqli_stmt_execute($stmt)) 
{
    // Récupérer l'ID de l'idée insérée
    $idee_id = mysqli_insert_id($connexion);

    // Insérer les informations du fichier dans la base de données
    if (!empty($_FILES['fichier']['name'])) {
        $fichierNom = $_FILES['fichier']['name'];
        $fichierType = $_FILES['fichier']['type'];
        $fichierTaille = $_FILES['fichier']['size'];
        $fichierContenu = file_get_contents($_FILES['fichier']['tmp_name']);

        $requete2 = "INSERT INTO fichier (nom_fichier, type, taille, contenu_fichier, idee_id) VALUES (?, ?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($connexion, $requete2);
        mysqli_stmt_bind_param($stmt2, "sssbi", $fichierNom, $fichierType, $fichierTaille, $fichierContenu, $idee_id);
        if (mysqli_stmt_execute($stmt2)) 
        {
            echo "<script>
                    alert('Nouvelle Idée Enregistrée');
                    window.location.href = '../../html/idee/AccueilIdee.php';
                </script>";
        } 
        else 
        {
            echo "Erreur lors de l'insertion du fichier: " . mysqli_error($connexion);
        }
        mysqli_stmt_close($stmt2);
    } else {
        echo "<script>
                alert('Nouvelle Idée Enregistrée');
                window.location.href = '../../html/idee/AccueilIdee.php';
            </script>";
    }
} 
else {
    echo "Erreur lors de l'insertion de l'idée: " . mysqli_error($connexion);
}

mysqli_stmt_close($stmt);
$connexion->close();
