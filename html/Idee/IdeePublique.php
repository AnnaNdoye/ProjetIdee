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

$connexion = mysqli_connect($host, $user, $password, $database);

if (!$connexion) {
    die("Erreur lors de la connexion: " . mysqli_connect_error());
}                  

$search = isset($_GET['search']) ? $_GET['search'] : ''; 
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : ''; 

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, employe.photo_profil, employe.prenom, employe.nom,
    (SELECT COUNT(*) FROM LikeIdee WHERE LikeIdee.idee_id = idee.id_idee) AS like_count,
    (SELECT COUNT(*) FROM LikeIdee WHERE LikeIdee.idee_id = idee.id_idee AND LikeIdee.employe_id = ?) AS user_liked,
    (SELECT COUNT(*) FROM Commentaire WHERE Commentaire.idee_id = idee.id_idee) AS commentaire_count
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN employe ON idee.employe_id = employe.id_employe
    WHERE idee.est_publique = 1 AND idee.titre LIKE ?
    ";

if ($filtre == 'Soumis' || $filtre == 'Approuvé' || $filtre == 'Rejeté' || $filtre == 'Implémenté') {
    $query .= " AND idee.statut = ?";
} elseif ($filtre == 'Plus') {
    $query .= " ORDER BY like_count DESC, idee.date_creation DESC";
} elseif ($filtre == 'Moins') {
    $query .= " ORDER BY like_count ASC, idee.date_creation DESC";
} else {
    $query .= " ORDER BY idee.date_creation DESC";
}

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$like_search = '%' . $search . '%';

if ($filtre == 'Soumis' || $filtre == 'Approuvé' || $filtre == 'Rejeté' || $filtre == 'Implémenté') {
    $stmt->bind_param('iss', $_SESSION['user_id'], $like_search, $filtre);
} else {
    $stmt->bind_param('is', $_SESSION['user_id'], $like_search);
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

<!-- j'ai utilisé un peu de bootstrap ça a aidé à amélioré les pages de mon codes sans trop d'éfforts -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idées Publiques</title>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" type="text/css" href="../../static/css/style1.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style5.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style.css">
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
            <form method="GET" action="IdeePublique.php">
                <input type="text" name="search" placeholder="Rechercher des idées" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
    </div>
    <div class="navigation">
        <strong><a href="AccueilIdee.php">Accueil</a></strong>
    </div>
    
    <div class="profil">
        <a href="Profil.php">
            <i class="fas fa-user-circle"></i>
            <strong>Profil</strong>
        </a>
    </div>
    <div class="connect_entete">
        <a href="../connexion.php">
            <i class="fas fa-user"></i>
            <span>Se déconnecter</span>
        </a>
    </div>
</div>

<form method="GET" action="IdeePublique.php" class="filtre" style="float: right;">
    <i class="fas fa-filter"></i> 
    <select name="filtre" id="filtre" onchange="this.form.submit()">
        <option value="">Date création</option>
        <optgroup label="Statut">
            <option value="Soumis" <?php echo $filtre == 'Soumis' ? 'selected' : ''; ?>>Soumis</option>
            <option value="Approuvé" <?php echo $filtre == 'Approuvé' ? 'selected' : ''; ?>>Approuvé</option>
            <option value="Rejeté" <?php echo $filtre == 'Rejeté' ? 'selected' : ''; ?>>Rejeté</option>
            <option value="Implémenté" <?php echo $filtre == 'Implémenté' ? 'selected' : ''; ?>>Implémenté</option>
        </optgroup>
        <optgroup label="Popularité">
            <option value="Plus" <?php echo $filtre == 'Plus' ? 'selected' : ''; ?>>Plus Liké</option>
            <option value="Moins" <?php echo $filtre == 'Moins' ? 'selected' : ''; ?>>Moins Liké</option>
        </optgroup>
    </select>
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
</form>

<div class="menu-deroulant">
    <button><strong>Menu</strong></button>
    <ul class="sous">
        <li><a href="AccueilIdee.php">Mes idées</a></li>
        <li><a href="NouvelleIdee.php"></i>Nouvelle Idee</a></li>
        <li><a href="Profil.php">Profil</a></li>
        <li><a href="../../database/deconnexion.php">Deconnexion</a></li>
    </ul>
</div>
<div class="container">
    <h1 id="ideepose"><?php echo $comment_count; ?> Idées Publiques</h1>
    <div id="ideas">
        <?php
        if ($result->num_rows > 0) 
        {
            while ($row = $result->fetch_assoc()) 
            {
        ?>
        <div class='enveloppe'> <!--j'avais fait en sorte que tout le div soit cliquable le onclick c'est du javascript avec "onclick="location.href='VoirIdeePublique.php?id=<?php echo htmlspecialchars($row['id_idee']); ?>'"" -->
            <div class='idea'>
                <div id="div1">
                    <p class="info">
                        <img src="<?php echo htmlspecialchars($row['photo_profil']); ?>" alt="Photo de profil" id="profile-img">
                        <?php echo htmlspecialchars($row['prenom']) . " " . htmlspecialchars($row['nom']); ?>
                    </p>
                    <p><strong>Créé le :</strong> <?php echo htmlspecialchars($row['date_creation']); ?></p>
                    <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($row['nom_categorie']); ?></p>
                </div>
                <div id="div2">
                    <span><h2>Titre : <?php echo htmlspecialchars($row['titre']); ?></h2></span>
                    <?php $statutClass = strtolower($row['statut']); ?>
                    <p class='status-<?php echo $statutClass; ?>'>Statut : <span class='status-circle'></span><?php echo htmlspecialchars($row['statut']); ?></p>
                </div>

                <div class='like-container'>
                    <form action='../../database/idee/like.php' method='POST'>
                        <input type='hidden' name='idee_id' value='<?php echo $row['id_idee']; ?>'>
                        <button type='submit' class='like-button<?php echo ($row['user_liked'] ? ' liked' : ''); ?>'>
                            <i class='fas fa-thumbs-up'></i>
                        </button>
                        <span class='like-count'><?php echo $row['like_count']; ?> like</span> 
                    </form>
                    <div class="online">
                    <i class='fas fa-comments'></i>
                    <span class='online'><?php echo $row['commentaire_count']; ?> commentaires</span>
                    </div>
                    
                </div>
                <a href='VoirIdeePublique.php?id=<?php echo $row['id_idee']; ?>'>Voir plus...</a>

            </div>
        </div> 
        <?php
            }
        } else {
            echo '<h2 id="ideepose">Aucune idée publique trouvée.</h2>';
        }
        
        $stmt->close();
        $connexion->close();
        ?>
    </div>
</div>

<div class="espace"></div>
<?php
    include("../barrefooter.html");
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.querySelector('.menu-deroulant button');
            const menuList = document.querySelector('.menu-deroulant ul');

            menuButton.addEventListener('click', () => {
                menuList.style.display = menuList.style.display === 'flex' ? 'none' : 'flex';
            });

            menuButton.addEventListener('mouseover', () => {
                menuList.style.display = 'flex';
            });

            menuButton.addEventListener('mouseout', () => {
                if (menuList.style.display !== 'flex') {
                    menuList.style.display = 'none';
                }
            });

            menuList.addEventListener('mouseover', () => {
                menuList.style.display = 'flex';
            });

            menuList.addEventListener('mouseout', () => {
                menuList.style.display = 'none';
            });
        });
</script>
</body>
</html>
