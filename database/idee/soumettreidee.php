<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Créer une connexion
$connexion = new mysqli($host, $user, $password, $database);

// Vérifier la connexion
if ($connexion->connect_error) 
{
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

//Je récupère les valeur du formulaire avec POST
$titre = $_POST['titre'];
$contenu = $_POST['contenu'];
$categorie = $_POST['categorie'];
$visibilite = $_POST['visibilite'] === 'publique' ? 1 : 0;
$fichier = $_FILES['fichier'];
// je définis le fuseau horaire
date_default_timezone_set('Europe/Paris');
// Récupérer la date courante au format YYYY-MM-DD hh:mm:ss qui va correspondre à DateTime dans ma base de données
$dateCourante = date('Y-m-d H:i:s');


// Gérer l'upload du fichier
$target_dir = "uploads/";
$target_fichier = $target_dir . basename($fichier["name"]);//
//move_uploaded_file($fichier["tmp_name"], $target_fichier);

$requete1 = "INSERT INTO idee (titre, description, est_publique, date_creation, date_modification, fichier) VALUES ('$titre', '$contenu', '$visibilite', '$dateCourante', '$dateCourante', '$target_fichier')";
$resultat1 = mysqli_query($connexion,$requete1);

if ($requete1) 
{
    echo "Nouvelle idée créée avec succès";
} 
else 
{
    echo "Erreur: La requête a écoué. " . $requete1 . "<br>" . $connexion->error;
}

$connexion->close();
