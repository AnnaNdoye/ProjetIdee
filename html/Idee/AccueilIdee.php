<?php
// Code PHP pour récupérer et traiter les données

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

$employe_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : '';

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    WHERE idee.employe_id = ? AND idee.titre LIKE ?
";

if ($filtre) {
    if ($filtre == 'Titre') {
        $query .= " ORDER BY idee.titre";
    } elseif ($filtre == 'Date de création') {
        $query .= " ORDER BY idee.date_creation";
    } elseif ($filtre == 'Statut') {
        $query .= " ORDER BY idee.statut";
    } elseif ($filtre == 'Visibilité') {
        $query .= " ORDER BY idee.est_publique";
    }
}

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}
$like_search = '%' . $search . '%';
$stmt->bind_param('is', $employe_id, $like_search);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}
$result = $stmt->get_result();

if (!$result) {
    die("Erreur lors de la requête: " . $connexion->error);
}
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
    <title>Accueil Idées</title>
    <script>
        function confirmDeletion(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette idée')) {
                window.location.href = '../../database/idee/supprimer_idee.php?id=' + id;
            }
        }

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

    <div class="menu-deroulant">
        <button><strong>Menu</strong></button>
        <ul class="sous">
            <li><a href="NouvelleIdee.php">Nouvelle Idée</a></li>
            <li><a href="AccueilIdee.php">Mes idées</a></li>
            <li><a href="IdeePublique.php">Idées publiques</a></li>
            <li><a href="Profil.php">Profil</a></li>
        </ul>
    </div>

    <div class="filtre">
        <i class="fas fa-filter"></i>
        <select name="filtre" id="filtre" onchange="this.form.submit()">
            <option value="">Filtrer par:</option>
            <option value="Date de création">Date de création</option>
            <option value="Statut">Statut</option>
            <option value="Privée">Privé</option>
            <option value="Publique">Publique</option>
        </select>
    </div>

    <div class="container">
        <div id="ideas">
            <?php
                if ($result->num_rows > 0) {
                    echo '<h1 id="ideepose">Toutes mes idées</h1>';
                while ($row = $result->fetch_assoc()) {
            ?>
                <div class="enveloppe">
                    <div class="idea">
                        <div id="div1">
                            <p><strong>Créé le: </strong> <?php echo htmlspecialchars($row['date_creation']); ?></p>
                            <p class="status-<?php echo strtolower(htmlspecialchars($row['statut'])); ?>">
                                <strong>Statut:</strong> <?php echo htmlspecialchars($row['statut']); ?> <span class="status-circle"></span>
                            </p>
                            <p><strong>Visibilité:</strong> <?php echo $row['est_publique'] == 1 ? 'Publique' : 'Privé'; ?></p>
                        </div>

                        <div id="div2">
                            <h2>Titre: <?php echo htmlspecialchars($row['titre']); ?></h2>
                            <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($row['nom_categorie']); ?></p>
                            <a href="VoirIdee.php?id=<?php echo htmlspecialchars($row['id_idee']); ?>">Voir plus...</a>
                        </div>
                    </div>
                    <div class="groupe">
                        <a class="edit" href="ModifierIdee.php?id=<?php echo htmlspecialchars($row['id_idee']); ?>"><i class="fas fa-edit"></i> Éditer</a>
                        <a class="supprime" href="javascript:void(0);" onclick="confirmDeletion(<?php echo htmlspecialchars($row['id_idee']); ?>)"><i class="fas fa-trash"></i> Supprimer</a>
                    </div>
                </div>
                <?php
                    }
                } 
                else {
                    echo '<h1 id="ideepose">Aucune idée pour le moment.</h1>';
                }
                ?>
        </div>
    </div>

    <button class="floating-button" onclick="location.href='NouvelleIdee.php'">
        <i class="fa fa-plus"></i>
    </button>

    <div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterSelect = document.getElementById('filtre');
            filterSelect.value = "<?php echo htmlspecialchars($filtre); ?>";
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$connexion->close();
?>
