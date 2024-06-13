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
$visibilite = $_POST['visibilite'] === 'publique' ? 1 : 0; // 1 pour publique (vrai), 0 pour privé (faux)
$fichier = $_FILES['fichier'];
$statut = "Soumis";

// Définir le fuseau horaire
date_default_timezone_set('Africa/Dakar');
$dateCourante = date('Y-m-d H:i:s');

// Récupérer l'ID de l'utilisateur
$employe_id = $_SESSION['user_id'];

// Gérer l'upload du fichier
$target_dir = "../../html/idee/upload_files/";
var_dump($_FILES['fichier']['name']);

$target_fichier = $target_dir . basename($fichier["name"]);
$fichierNom = basename($fichier["name"]);
$fichierType = $fichier["type"];
$fichierTaille = $fichier["size"];

function upload_file(){
    if(!empty($_FILES)){
        $nom = $_FILES['file']['name'] ;
        $extension =str_replace('.', '',strrchr($nom,'.'));
        $tmp_file = $_FILES['file']['tmp_name'];
        $extension_autoriser = array("jpg","png","jpeg");
        if(in_array($extension, $extension_autoriser)){
            if($_FILES['file']['size']!=0){
                if(move_uploaded_file($tmp_file,ROOT.'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.''.$nom)){ 
                }
            }
        }
    }
}

if (move_uploaded_file($_FILES['fichier']['name'], $target_fichier)) 
{
    // Insérer l'idée dans la base de données
    $requete1 = "INSERT INTO idee (titre, contenu_idee, est_publique, date_creation, date_modification, employe_id, categorie_id, statut) VALUES ('$titre', '$contenu', '$visibilite', '$dateCourante', '$dateCourante', '$employe_id', '$categorie_id', '$statut')";
    if (mysqli_query($connexion, $requete1)) 
    {
        // Récupérer l'ID de l'idée insérée
        $idee_id = mysqli_insert_id($connexion);

        // Insérer les informations du fichier dans la base de données
        $requete2 = "INSERT INTO fichier (nom_fichier, type, taille, idee_id) VALUES ('$fichierNom', '$fichierType', '$fichierTaille', '$idee_id')";
        if (mysqli_query($connexion, $requete2)) 
        {
            echo "<script>
                    alert('Idée soumise avec succès');
                    window.location.href = '../NouvelleIdee.php';
                </script>";
        } 
        else 
        {
            echo "Erreur lors de l'insertion du fichier: " . mysqli_error($connexion);
        }
    } 
    else {
        echo "Erreur lors de l'insertion de l'idée: " . mysqli_error($connexion);
    }
} 
else {
    echo "Erreur lors de l'upload du fichier.";
}



$connexion->close();