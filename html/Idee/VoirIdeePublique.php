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
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier,
    employe.nom, employe.prenom, employe.photo_profil
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    LEFT JOIN employe ON idee.employe_id = employe.id_employe
    WHERE idee.id_idee = ?
";

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$stmt->bind_param('i', $idee_id);
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
    <link rel="stylesheet" href="styles.css">
    <title>Voir Idée</title>
    <style>
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .idea-details {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background: #f9f9f9;
            position: relative;
        }
        .creator-info {
            display: flex;
            align-items: center;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .creator-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .creator-info .name {
            font-weight: bold;
        }
        .idea-dates {
            text-align: right;
            font-size: 0.9em;
            color: #666;
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .status.soumis { background: #ffecb3; }
        .status.approuve { background: #c8e6c9; }
        .status.rejete { background: #ffcdd2; }
        .status.implemente { background: #b3e5fc; }
        .like-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .like-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            transition: transform 0.2s ease;
        }
        .like-button:focus {
            outline: none;
        }
        .thumb-icon {
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .like-button:hover .thumb-icon {
            color: #888;
            transform: scale(1.1);
        }
        .liked .thumb-icon {
            color: #007BFF;
            transform: scale(1.2);
        }
        .like-count {
            margin-left: 10px;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }
        .liked + .like-count {
            color: #007BFF;
        }
        .comments-section {
            margin-top: 40px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            resize: vertical;
            margin-bottom: 10px;
        }
        .comment-form button {
            background: #ff7f00;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .comment-form button:hover {
            background: #e06b00;
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
        <div class="navigation">
            <strong>
                <a href="MesIdees.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </strong>
        </div>
    </div>

    <div class="container">
        <h1>Voir Idée</h1>
        <div class="idea-details">
            <div class="creator-info">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($idee['photo_profil']); ?>" alt="Photo de profil" id="profile-img">
                <div>
                    <span class="name"><?php echo htmlspecialchars($idee['prenom']) . ' ' . htmlspecialchars($idee['nom']); ?></span>
                    <div class="idea-dates">
                        <p><strong>Créé le:</strong> <?php echo htmlspecialchars($idee['date_creation']); ?></p>
                        <p><strong>Modifié le:</strong> <?php echo htmlspecialchars($idee['date_modification']); ?></p>
                    </div>
                </div>
            </div>
            <div class="idea-info">
                <p class="status <?php echo strtolower(htmlspecialchars($idee['statut'])); ?>">
                    <strong>Statut:</strong> <?php echo htmlspecialchars($idee['statut']); ?>
                </p>
                <h2><?php echo htmlspecialchars($idee['titre']); ?></h2>
                <hr>
                <p><?php echo nl2br(htmlspecialchars($idee['contenu_idee'])); ?></p>
                <?php if ($idee['nom_fichier']) : ?>
                    <p><strong>Fichier :</strong> <a href="data:<?php echo htmlspecialchars($idee['type']); ?>;base64,<?php echo base64_encode($idee['contenu_fichier']); ?>" target="_blank"><?php echo htmlspecialchars($idee['nom_fichier']); ?></a></p>
                <?php endif; ?>
                <div class="like-container">
                    <button id="likeButton" class="like-button">
                        <span id="thumbIcon" class="thumb-icon"><i class="far fa-thumbs-up"></i></span>
                    </button>
                    <span id="likeCount" class="like-count">0</span>
                </div>
            </div>
        </div>

        <div class="comments-section">
            <h3>Commentaires</h3>
            <div class="comment-form">
                <textarea placeholder="Ajouter un commentaire"></textarea>
                <button>Envoyer</button>
            </div>
        </div>
    </div>

    <div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const likeButton = document.getElementById('likeButton');
            const likeCount = document.getElementById('likeCount');
            const thumbIcon = document.querySelector('.thumb-icon i');
            let isLiked = false;
            let count = 0;

            likeButton.addEventListener('click', () => {
                if (isLiked) {
                    count--;
                    likeButton.classList.remove('liked');
                    thumbIcon.classList.remove('fas');
                    thumbIcon.classList.add('far');
                } else {
                    count++;
                    likeButton.classList.add('liked');
                    thumbIcon.classList.remove('far');
                    thumbIcon.classList.add('fas');
                }
                isLiked = !isLiked;
                likeCount.textContent = count;
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$connexion->close();
?>
