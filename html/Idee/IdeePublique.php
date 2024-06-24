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

// Créer une connexion
$connexion = mysqli_connect($host, $user, $password, $database);

// Vérifier la connexion
if (!$connexion) {
    die("Erreur lors de la connexion: " . mysqli_connect_error());
}

$search = isset($_GET['search']) ? $_GET['search'] : ''; 

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, employe.photo_profil, employe.prenom, employe.nom,
    (SELECT COUNT(*) FROM LikeIdee WHERE LikeIdee.idee_id = idee.id_idee) AS like_count,
    (SELECT COUNT(*) FROM LikeIdee WHERE LikeIdee.idee_id = idee.id_idee AND LikeIdee.employe_id = ?) AS user_liked
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN employe ON idee.employe_id = employe.id_employe
    WHERE idee.est_publique = 1 AND idee.titre LIKE ?
    ORDER BY idee.date_creation DESC";

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}
$like_search = '%' . $search . '%';
$stmt->bind_param('is', $_SESSION['user_id'], $like_search);
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
    <title>Idées Publiques</title>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" type="text/css" href="../../static/css/style1.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style5.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style.css">
    <style>
        .filtre select {
            width: 100px;
            transition: width 0.5s;
        }
        .filtre select:focus {
            width: 200px;
        }
    </style>
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
    <div class="search-bar">
        <form method="GET" action="IdeePublique.php">
            <input type="text" name="search" placeholder="Rechercher des idées publiques..." value="<?php echo htmlspecialchars($search); ?>">
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
        <option value="">Filtrer par:</option>
        <option value="Statut" <?php if ($filtre == 'Statut') echo 'selected'; ?>>Statut</option>
        <option value="Date de création" <?php if ($filtre == 'Date de création') echo 'selected'; ?>>Plus populaire</option>
    </select>
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
</form>

<div class="menu-deroulant">
    <button><strong>Menu</strong></button>
    <ul class="sous">
        <li><a href="AccueilIdee.php">Mes idées</a></li>
        <li><a href="NouvelleIdee.php">Nouvelle Idee</a></li>
        <li><a href="Profil.php">Profil</a></li>
    </ul>
</div>

<div class="container">
    <h1 id="ideepose"><?php echo $comment_count; ?> Idées Publiques </h1>
    <div id="ideas">
        <?php
        if ($result->num_rows > 0)
        {
            while ($row = $result->fetch_assoc()) 
            {
        ?>
        <div class='enveloppe'>
            <div class='idea' onclick="location.href='VoirIdeePublique.php?id=<?php echo htmlspecialchars($row['id_idee']); ?>'">
                <div id="div1"> 
                    <p class="info"><strong>Par :</strong> 
                        <img src="<?php echo htmlspecialchars($row['photo_profil']); ?>" alt="Photo de profil" id="profile-img">
                        <?php echo htmlspecialchars($row['prenom']) . " " . htmlspecialchars($row['nom']); ?>
                    </p>
                    <p><strong>Créé le :</strong> <?php echo htmlspecialchars($row['date_creation']); ?></p>
                    <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($row['nom_categorie']); ?></p>
                </div>

                <div id="div2">
                    <span>Titre :<h2><?php echo htmlspecialchars($row['titre']); ?></h2></span>
                    <?php $statutClass = strtolower($row['statut']); ?>
                    <p class='status-<?php echo $statutClass; ?>'>Statut : <span class='status-circle'></span><?php echo htmlspecialchars($row['statut']); ?></p>
                </div>

                <div class='like-container'>
                    <form action='../../database/idee/like.php' method='POST'>
                        <input type='hidden' name='idee_id' value='<?php echo $row['id_idee']; ?>'>
                        <button type='submit' class='like-button<?php echo ($row['user_liked'] ? ' liked' : ''); ?>'>
                            <i class='fas fa-thumbs-up thumb-icon'></i>
                        </button>
                        <span class='like-count'><?php echo $row['like_count']; ?> like</span> 
                    </form>
                </div>
            </div>
            <a href='VoirIdeePublique.php?id=<?php echo $row['id_idee']; ?>'>Voir plus...</a>
        </div> 
        <?php
            }
        } else {
            echo "<p>Aucune idée publique trouvée.</p>";
        }
        
        $stmt->close();
        $connexion->close();
        ?>
    </div>
</div>

<div class="espace"></div>
<div class="footer">
    <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
    <h4 class="footer-right">© Orange/Juin2024</h4>
</div>
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
