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

// Récupérer les détails de l'idée
$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier,
    employe.nom, employe.prenom, employe.photo_profil,
    (SELECT COUNT(*) FROM LikeIdee WHERE idee_id = idee.id_idee) AS like_count,
    (SELECT COUNT(*) FROM LikeIdee WHERE idee_id = idee.id_idee AND employe_id = ?) AS user_liked
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

$stmt->bind_param('ii', $employe_id, $idee_id);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Idée non trouvée ou vous n'êtes pas autorisé à la voir.");
}

$idee = $result->fetch_assoc();

// Récupérer les commentaires
$comments_query = "
    SELECT commentaire.id_commentaire, commentaire.contenu, commentaire.date_creation, commentaire.date_modification, 
    employe.nom, employe.prenom, employe.photo_profil
    FROM commentaire
    LEFT JOIN employe ON commentaire.employe_id = employe.id_employe
    WHERE commentaire.idee_id = ?
    ORDER BY commentaire.date_creation DESC
";

$comments_stmt = $connexion->prepare($comments_query);
if ($comments_stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$comments_stmt->bind_param('i', $idee_id);
if (!$comments_stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $comments_stmt->error);
}

$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);

// Ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $comment_content = $_POST['comment_content'];
    $comment_query = "
        INSERT INTO commentaire (contenu, employe_id, idee_id) 
        VALUES (?, ?, ?)
    ";
    $comment_stmt = $connexion->prepare($comment_query);
    if ($comment_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $comment_stmt->bind_param('sii', $comment_content, $employe_id, $idee_id);
    if (!$comment_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $comment_stmt->error);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Gérer les likes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    $like = $_POST['like'];
    
    if ($like == 'true') {
        $like_query = "
            INSERT INTO LikeIdee (employe_id, idee_id) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE employe_id = employe_id
        ";
    } else {
        $like_query = "
            DELETE FROM LikeIdee WHERE employe_id = ? AND idee_id = ?
        ";
    }

    $like_stmt = $connexion->prepare($like_query);
    if ($like_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $like_stmt->bind_param('ii', $employe_id, $idee_id);
    if (!$like_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $like_stmt->error);
    }

    $like_stmt->close();
    exit(json_encode(['success' => true]));
}
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
            max-width: 1500px;
            margin: auto;
            padding: 20px;
        }
        .idea-details {
            border: 1px solid #ddd;
            padding: 100px;
            border-radius: 8px;
            background: #f9f9f9;
            position: relative;
        }
        .creator-info {
            display: flex;
            align-items: center;
            position: absolute;
            top: 10px;
            left: 10px;
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
            font-weight: bolder;
            top: 10px;
            right: 10px;
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
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .comment-form button:hover {
            background-color: #0056b3;
        }
        .comment {
            display: flex;
            align-items: flex-start;
            padding: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .comment img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .comment-content {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }
        .comment-meta {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="idea-details">
            <div class="creator-info">
                <img src="data:image/jpeg;base64,<?= base64_encode($idee['photo_profil']) ?>" alt="Photo de profil">
                <div class="name"><?= htmlspecialchars($idee['prenom']) ?> <?= htmlspecialchars($idee['nom']) ?></div>
            </div>
            <div class="status <?= strtolower($idee['statut']) ?>">
                <?= htmlspecialchars($idee['statut']) ?>
            </div>
            <h1><?= htmlspecialchars($idee['titre']) ?></h1>
            <div class="idea-dates">
                Créée le: <?= htmlspecialchars($idee['date_creation']) ?>
                <br>
                Dernière modification: <?= htmlspecialchars($idee['date_modification']) ?>
            </div>
            <p><?= nl2br(htmlspecialchars($idee['contenu_idee'])) ?></p>
            <?php if ($idee['nom_fichier']): ?>
                <div class="file">
                    <strong>Fichier joint:</strong>
                    <a href="data:<?= $idee['type'] ?>;base64,<?= base64_encode($idee['contenu_fichier']) ?>" download="<?= htmlspecialchars($idee['nom_fichier']) ?>">
                        <?= htmlspecialchars($idee['nom_fichier']) ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="like-container">
                <button class="like-button <?= $idee['user_liked'] ? 'liked' : '' ?>" data-liked="<?= $idee['user_liked'] ? 'true' : 'false' ?>">
                    <i class="fas fa-thumbs-up thumb-icon"></i>
                </button>
                <div class="like-count"><?= $idee['like_count'] ?></div>
            </div>
        </div>
        <div class="comments-section">
            <h2>Commentaires</h2>
            <div class="comment-form">
                <form method="POST">
                    <textarea name="comment_content" rows="3" placeholder="Ajouter un commentaire..." required></textarea>
                    <button type="submit">Envoyer</button>
                </form>
            </div>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <img src="data:image/jpeg;base64,<?= base64_encode($comment['photo_profil']) ?>" alt="Photo de profil">
                    <div class="comment-content">
                        <div class="comment-meta">
                            <strong><?= htmlspecialchars($comment['prenom']) ?> <?= htmlspecialchars($comment['nom']) ?></strong>
                            <br>
                            <?= htmlspecialchars($comment['date_creation']) ?>
                        </div>
                        <p><?= nl2br(htmlspecialchars($comment['contenu'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const likeButton = document.querySelector('.like-button');
            const likeCount = document.querySelector('.like-count');

            likeButton.addEventListener('click', () => {
                const liked = likeButton.dataset.liked === 'true';

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        like: !liked
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        likeButton.dataset.liked = !liked;
                        likeButton.classList.toggle('liked', !liked);
                        likeCount.textContent = parseInt(likeCount.textContent) + (!liked ? 1 : -1);
                    }
                });
            });
        });
    </script>
</body>
</html>
