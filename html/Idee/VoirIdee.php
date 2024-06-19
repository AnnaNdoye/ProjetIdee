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
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <link rel="stylesheet" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" href="styles.css">
    <title>Voir Idée</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='../accueil.html'">
            <img src="../../static/img/icon.png" alt="Logo">
            <div>
                <h1>Orange</h1>
                <h3><span class="for-ideas">for ideas</span></h3>
            </div>
        </div>
        <div class="navigation">
            <strong>
                <a href="MesIdees.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </strong>
        </div>
    </div>

    <div class="container">
        <h1>Voir Idée</h1>
        <div class="idea-details">
            <h2><?php echo htmlspecialchars($idee['titre']); ?></h2>
            <p><strong>Contenu:</strong> <?php echo nl2br(htmlspecialchars($idee['contenu_idee'])); ?></p>
            <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($idee['nom_categorie']); ?></p>
            <?php if ($idee['nom_fichier']) : ?>
                <p><strong>Fichier :</strong> <a href="data:<?php echo htmlspecialchars($idee['type']); ?>;base64,<?php echo base64_encode($idee['contenu_fichier']); ?>" target="_blank"><?php echo htmlspecialchars($idee['nom_fichier']); ?></a></p>
            <?php endif; ?>
            <p><strong>Date de création:</strong> <?php echo htmlspecialchars($idee['date_creation']); ?></p>
            <p><strong>Date de modification:</strong> <?php echo htmlspecialchars($idee['date_modification']); ?></p>
            <p class="status-<?php echo strtolower(htmlspecialchars($idee['statut'])); ?>">
                <strong>Statut:</strong> <?php echo htmlspecialchars($idee['statut']); ?> <span class="status-circle"></span>
            </p>
            <p><strong>Visibilité:</strong> <?php echo $idee['est_publique'] == 1 ? 'Publique' : 'Privé'; ?></p>
            <a href="ModifierIdee.php?id=<?php echo htmlspecialchars($idee['id_idee']); ?>" class="edit-button"><i class="fas fa-edit"></i> Éditer</a>
        </div>
    </div>

    <div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>
</body>
</html>

<?php
$stmt->close();
$connexion->close();
?>
