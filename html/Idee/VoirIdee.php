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

if (!isset($_GET['id'])) {
    die("ID de l'idée non spécifié.");
}

$idee_id = $_GET['id'];
$employe_id = $_SESSION['user_id'];

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    WHERE idee.id_idee = ? AND idee.employe_id = ?
";

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$stmt->bind_param('ii', $idee_id, $employe_id);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Idée non trouvée ou vous n'êtes pas autorisé à la voir.");
}

$idee = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" type="text/css" href="../../static/css/style1.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style5.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/VoirIdeePrive.css">
    <title>Voir Idée</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='../accueil.html'">
            <img src="../../static/img/icon.png" alt="Logo">
            <div class="logo">
                <h1>Orange</h1>
                <h3><span class="for-ideas">for ideas</span></h3>
            </div>
        </div>

        <div class="connect_entete">
            <a href="../../database/deconnexion.php">
                <i class="fas fa-user"></i>
                <span>Se déconnecter</span>
            </a>
        </div>

        <div class="navigation" style="background-color: #f1f1f1; border-radius: 5px; padding: 10px 20px">
            <strong>
                <a href="AccueilIdee.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </strong>
        </div>
    </div>

    <div class="container">
        <div id="enhauteur">
            <p><strong>Créer le :</strong> <?php echo htmlspecialchars($idee['date_creation']); ?></p>
            <p><strong>Modifier le :</strong> <?php echo htmlspecialchars($idee['date_modification']); ?></p>
            <p class="status-<?php echo strtolower(htmlspecialchars($idee['statut'])); ?>">
                <strong>Statut:</strong> <?php echo htmlspecialchars($idee['statut']); ?> <span class="status-circle"></span>
            </p>
            <p><strong>Visibilité:</strong> <?php echo $idee['est_publique'] == 1 ? 'Publique' : 'Privé'; ?></p>
        </div>

        <div class="details">
            <h4>Titre : </h4><h2><?php echo htmlspecialchars($idee['titre']); ?></h2> 
        </div>

        <div class="details">
            <p><strong>Contenu:</strong> <?php echo $idee['contenu_idee']; ?></p>
        </div>

        <div class="details">
            <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($idee['nom_categorie']); ?></p>
        </div>
            
        <div class="details">
            <p>Fichier</p>
            <?php if ($idee['nom_fichier']) : 
                $file = "../../database/idee/" . $idee['contenu_fichier'];
                $file_extension = pathinfo($file, PATHINFO_EXTENSION);
            ?>
                <?php if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'jpe', 'avi'])) : ?>
                    <img src="<?php echo $file; ?>" alt="Fichier" style="max-width: 200px;">
                    <a class="download-button" href="<?php echo $file; ?>" download>Télécharger</a>
                <?php elseif (in_array($file_extension, ['pdf'])) : ?>
                    <embed src="<?php echo $file; ?>" type="application/pdf" width="600" height="600">
                    <a class="download-button" href="<?php echo $file; ?>" download>Télécharger</a>
                <?php elseif (in_array($file_extension, ['doc', 'docx', '.mpp', '.gz','.zip', '.odp', '.odt', '.ods', '.xlsx', '.pptx', '.txt'])) : ?>
                    <p><a href="<?php echo $file; ?>" target="_blank">Voir le document Word</a></p>
                <?php else : ?>
                <p>Type de fichier non supporté pour l'affichage</p>
            <?php endif; ?>
            <br>
            <?php endif; ?>
        </div>

        <a class="edit" id="editer" href="ModifierIdee.php?id=<?php echo htmlspecialchars($idee['id_idee']); ?>" class="edit-button"><i class="fas fa-edit"></i> Éditer</a>
    </div>

    <div class="espace"></div>
    <?php
        include("../barrefooter.html");
    ?>
</body>
</html>

<?php
$stmt->close();
$connexion->close();
?>
