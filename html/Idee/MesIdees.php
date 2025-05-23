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

$employe_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    WHERE idee.employe_id = ? AND idee.titre LIKE ?";
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
    <title>Mes Idées</title>
    <link rel="icon" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <link rel="stylesheet" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .search-bar form{
            display: flex;
            align-items: center;
            border-radius: 5px;
            overflow: hidden;
            margin-left: 20px;
            flex: 1;
            border: #FF6600 solid;
            outline: none;
            width: 100%;
        }

        form input{
            border: none;
        padding: 10px;
        outline: none;
        color: #000;
        width: 100%;
        }

        form button {
        background: #fff;
        border: none;
        color: #FF6600;
        padding: 10px 15px;
        cursor: pointer;
        }

        .connect_entete a, .profil a {
            color: #ff6600;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .connect_entete a:hover, .profil a:hover {
            color: #000;
        }

        .idea {
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 900px;
        }

        .idea:hover {
            transform: translateY(-5px);
        }

        .idea h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.5em;
        }

        .idea p {
            margin-bottom: 10px;
            color: #555;
        }

        .idea .status-circle {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-soumis .status-circle {
            background-color: #F39C12;
        }

        .status-approuve .status-circle {
            background-color: #27AE60;
        }

        .status-rejete .status-circle {
            background-color: #E74C3C;
        }

        .status-implemente .status-circle {
            background-color: #3498DB;
        }

        .idea a {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .idea a:hover {
            background-color: #0056b3;
        }

        .idea i {
            margin-right: 5px;
        }

        .supprime {
            background-color: #E74C3C;
        }

        .enveloppe {
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
        }

        @media screen and (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                margin: 10px 0;
                width: 100%;
            }

            .navigation a {
                margin-top: 10px;
            }

            .ideas {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
    .header h1 {
        font-size: 20px;
    }

    .form input {
        width: 150px;
    }

    .form input:focus {
        width: 200px;
    }
}
    </style>
    <script>
        function confirmDeletion(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette idée ?')) {
                window.location.href = '../../database/idee/supprimer_idee.php?id=' + id;
            }
        }
    </script>
</head>
<body>
<div class="header">
    <div class="logo" onclick="location.href='../Accueil.html'">
        <img src="../../static/img/icon.png" alt="Logo">
        <div>
            <h1>Orange</h1>
            <h3><span class="for-ideas"> for ideas</span></h3>
        </div>
    </div>
    <div class="search-bar">
        <form method="GET" action="MesIdees.php">
            <input type="text" name="search" placeholder="Rechercher des idées" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="navigation">
        <strong>
            <a href="NouvelleIdee.php"><i class="fa fa-plus-circle"></i> Nouvelle idée</a>
        </strong>
    </div>
    <div class="connect_entete">
        <a href="../connexion.php">
            <i class="fas fa-user"></i>
            <span>Se déconnecter</span>
        </a>
    </div>
    <div class="profil">
        <a href="Profil.php">
            <i class="fas fa-user-circle"></i>
            <strong>Profil</strong>
        </a>
    </div>
</div>


<div class="filtre">
    <i class="fa-solid fa-filter"></i>
    <select name="filtre">
        <option>Filtrer par :</option>
        <option value="Titre">Titre</option>
        <option value="Date">Date Création</option>
        <option value="Statut">Statut</option>
    </select>
</div>

<div class="container">
    <h1>Mes Idées</h1>
    <div class="ideas">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="enveloppe">
                
                <div class="idea">
                    <h2>Titre: <?php echo htmlspecialchars($row['titre']); ?></h2>
                </div>
                <div class="idea">
                    <p><strong>Contenu :</strong> <?php echo htmlspecialchars($row['contenu_idee']); ?></p>
                </div>
                <div class="idea">
                    <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($row['nom_categorie']); ?></p>
                    <?php if ($row['nom_fichier']) : ?>
                        <?php echo '<img src="data:'.htmlspecialchars($row['type']).';base64,'.base64_encode($row['contenu_fichier']).'"/>'; ?>
                    <?php endif; ?>
                    <p><strong>Date de création: </strong> <?php echo htmlspecialchars($row['date_creation']); ?></p>
                    <p><strong>Date de modification: </strong> <?php echo htmlspecialchars($row['date_modification']); ?></p>
                    <p class="status-<?php echo strtolower(htmlspecialchars($row['statut'])); ?>">
                        <strong>Statut:</strong> <strong class="statut-color"> <?php echo htmlspecialchars($row['statut']); ?> <span class="status-circle"></span></strong>
                    </p>
                    <?php 
                    if ($row['est_publique'] == 1) {
                        $visibilite = "publique";
                    } else {
                        $visibilite = "privé";
                    }
                    ?>
                    <p><strong>Visibilité:</strong> <?php echo $visibilite; ?></p>
                    <a href="ModifierIdee.php?id=<?php echo htmlspecialchars($row['id_idee']); ?>"><i class="fas fa-edit"></i> Éditer</a>
                    <a class="supprime" href="javascript:void(0);" onclick="confirmDeletion(<?php echo htmlspecialchars($row['id_idee']); ?>)"><i class="fas fa-trash"></i> Supprimer</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
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
