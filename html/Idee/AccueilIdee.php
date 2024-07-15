<?php
session_start();

if (!isset($_SESSION['user_id']) ) {
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

$employe_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : '';

// Construction de la requête SQL avec les filtres
$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    WHERE idee.employe_id = ? AND idee.titre LIKE ?
";

if ($filtre) {
    if ($filtre == 'Publique' || $filtre == 'Privé') {
        $query .= " AND idee.est_publique = ?";
    } else {
        $query .= " AND idee.statut = ?";
    }
}

$query .= " ORDER BY idee.date_creation DESC";

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$like_search = '%' . $search . '%';

if ($filtre) {
    if ($filtre == 'Publique') {
        $est_publique = 1;
        $stmt->bind_param('isi', $employe_id, $like_search, $est_publique);
    } elseif ($filtre == 'Privé') {
        $est_publique = 0;
        $stmt->bind_param('isi', $employe_id, $like_search, $est_publique);
    } else {
        $stmt->bind_param('iss', $employe_id, $like_search, $filtre);
    }
} else {
    $stmt->bind_param('is', $employe_id, $like_search);
}

if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}
$result = $stmt->get_result();

if (!$result) {
    die("Erreur lors de la requête: " . $connexion->error);
}

$comment_count = $result->num_rows;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" type="text/css" href="../../static/css/style1.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style5.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style.css">
    <script type="text/javascript" src="../../static/js/script3.js"
    <title>Accueil Idées</title>
    <script>
        
    </script>
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
        <div class="search-bar">
            <form method="GET" action="AccueilIdee.php">
                <input type="text" name="search" placeholder="Rechercher des idées" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="navigation">
            <strong>
                <a href="NouvelleIdee.php"><i class="fa fa-plus-circle"></i> Nouvelle idée</a>
            </strong>
        </div>
        <div class="profil">
            <a href="Profil.php">
                <i class="fas fa-user-circle"></i>
                <strong>Profil</strong>
            </a>
        </div>
        <div class="connect_entete">
            <a href="../../database/deconnexion.php">
                <i class="fas fa-user"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </div>

    <form method="GET" action="AccueilIdee.php" class="filtre" style="float: right;">
        <i class="fas fa-filter"></i> 
        <select class="selectionne" name="filtre" id="filtre" onchange="this.form.submit()">
            <option value="">Date création</option>
            <optgroup label="Statut">
                <option value="Soumis" <?php echo $filtre == 'Soumis' ? 'selected' : ''; ?>>Soumis</option>
                <option value="Approuvé" <?php echo $filtre == 'Approuvé' ? 'selected' : ''; ?>>Approuvé</option>
                <option value="Rejeté" <?php echo $filtre == 'Rejeté' ? 'selected' : ''; ?>>Rejeté</option>
                <option value="Implémenté" <?php echo $filtre == 'Implémenté' ? 'selected' : ''; ?>>Implémenté</option>
            </optgroup>
            <optgroup label="Visibilité">
                <option value="Publique" <?php echo $filtre == 'Publique' ? 'selected' : ''; ?>>Publique</option>
                <option value="Privé" <?php echo $filtre == 'Privé' ? 'selected' : ''; ?>>Privé</option>
            </optgroup>
        </select>
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
    </form>

    <div class="menu-deroulant">
        <button><strong>Menu</strong></button>
        <ul class="sous">
            <li><a href="NouvelleIdee.php">Nouvelle Idée</a></li>
            <li><a href="AccueilIdee.php">Mes idées</a></li>
            <li><a href="IdeePublique.php">Idées publiques</a></li>
            <li><a href="Profil.php">Profil</a></li>
            <li><a href="../../database/deconnexion.php">Deconnexion</a></li>
        </ul>
    </div>

    <div class="container">
        <div id="ideas">
            <?php
                if ($result->num_rows > 0) {
                    echo '<h1 id="ideepose">Toutes mes idées (' .$comment_count. ')</h1>'; 
                    while ($row = $result->fetch_assoc()) {
            ?>
                <div class="enveloppe">
                    <div class="idea" type="width:1000px;">
                        <div id="div1">
                            <p><strong>Créé le: </strong> <?php echo htmlspecialchars($row['date_creation']); ?></p>
                            <p class="status-<?php echo strtolower(htmlspecialchars($row['statut'])); ?>">
                                <strong>Statut:</strong> <?php echo htmlspecialchars($row['statut']); ?> <span class="status-circle"></span>
                            </p>
                            <p><strong>Visibilité:</strong> <?php echo $row['est_publique'] == 1 ? 'Publique' : 'Privé'; ?></p>
                        </div>
                        <div id="div2">
                            <h2><?php echo htmlspecialchars($row['titre']); ?></h2>
                            <p class="categorie"><strong>Catégorie:</strong> <?php echo htmlspecialchars($row['nom_categorie']); ?></p>
                            <p class="paragraphe"><?php echo nl2br($row['contenu_idee']); ?></p>
                            
                        </div>
                        <div id="div3">
                            <p class="icons">
                                <a id="modifier" href="ModifierIdee.php?id=<?php echo $row['id_idee']; ?>"><i class="fas fa-edit"> Modifier</i></a>
                                <a id="supprimer" href="javascript:confirmDeletion(<?php echo $row['id_idee']; ?>)"><i class="fas fa-trash-alt"> Supprimer</i></a>
                                <a id="voir" href="<?php echo $row['est_publique'] == 1 ? 'VoirIdeePubliqueEdit.php' : 'VoirIdee.php'; ?>?id=<?php echo $row['id_idee']; ?>"><i class="fas fa-eye"> Voir</i></a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php
                    }
                } else {
                    echo '<h2 id="ideepose">Aucune idée trouvée.</h2>';
                }
            ?>
        </div>
    </div>

    <button class="floating-button" onclick="location.href='NouvelleIdee.php'">
        <i class="fa fa-plus"></i>
    </button>
    
    <div class="espace"></div>
    <?php
        include("../barrefooter.html");
    ?>
    <script>
        function confirmDeletion(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette idée ?')) {
        window.location.href = '../../database/idee/supprimer_idee.php?id=' + id;
    }
}
    </script>
</body>
</html>

<?php
$connexion->close();
?>
