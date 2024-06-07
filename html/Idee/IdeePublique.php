<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <title>Idées Publiques</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #FF6600;
            color: #fff;
        }
        .header .logo {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .header .logo img {
            height: 50px;
            margin-right: 10px;
        }
        .header .logo h1 {
            margin: 0;
        }
        .ideas-list {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .idea {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .idea h3 {
            margin-top: 0;
        }
        .idea-details {
            display: none;
            flex-direction: column;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .idea img, .comment img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .comments {
            margin-top: 20px;
        }
        .comment {
            display: flex;
            margin-top: 10px;
        }
        .like-button {
            background-color: #FF6600;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        .comment-input {
            margin-top: 20px;
            display: flex;
        }
        .comment-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .comment-input button {
            background-color: #FF6600;
            color: white;
            border: none;
            padding: 10px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='../accueil.html'">
            <img src="../../static/img/icon.png" alt="Logo">
            <h1>Orange</h1>
        </div>
        <div class="navigation">
            <a href="../accueil.html">Accueil</a>
            <a href="../new_idea.html">+ Nouvelle idée</a>
        </div>
        <div class="connect_entete">
            <a href="../Connexion.php">
                <i class="fas fa-user"></i>
                <strong>Se déconnecter</strong>
            </a>
        </div>
        <div class="profil">
            <i class="fas fa-user-circle"></i>
            <p>Profil</p>
        </div>
    </div>

    <div class="ideas-list">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="idea" data-id="<?= $row['id'] ?>">
                <h3><?= $row['title'] ?></h3>
                <p><?= $row['content'] ?></p>
                <p><strong>Catégorie:</strong> <?= $row['category'] ?></p>
                <p><strong>Date de création:</strong> <?= $row['created_at'] ?></p>
                <p><strong>Date de modification:</strong> <?= $row['updated_at'] ?></p>
                <p><strong>Auteur:</strong> <img src="<?= $row['profile_picture'] ?>" alt="Profile Picture"> <?= $row['author'] ?></p>
                <a href="<?= $row['attachment'] ?>" target="_blank">Voir le fichier joint</a>
                <button class="like-button">Like (<span class="like-count"><?= $row['likes'] ?></span>)</button>
            </div>
            <div class="idea-details" id="idea-details-<?= $row['id'] ?>">
                <h3><?= $row['title'] ?></h3>
                <p><?= $row['content'] ?></p>
                <a href="<?= $row['attachment'] ?>" target="_blank">Voir le fichier joint</a>
                <div class="comments">
                    <h4>Commentaires</h4>
                    <?php if(isset($comments[$row['id']])): ?>
                        <?php foreach($comments[$row['id']] as $comment): ?>
                            <div class="comment">
                                <img src="<?= $comment['profile_picture'] ?>" alt="Profile Picture">
                                <div>
                                    <p><strong><?= $comment['author'] ?>:</strong> <?= $comment['content'] ?></p>
                                    <p><small><?= $comment['created_at'] ?></small></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="comment-input">
                    <input type="text" placeholder="Ajouter un commentaire...">
                    <button>Commenter</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.idea').forEach(idea => {
                idea.addEventListener('click', () => {
                    const ideaDetails = document.querySelector('#idea-details-' + idea.dataset.id);
                    ideaDetails.style.display = ideaDetails.style.display === 'none' || !ideaDetails.style.display ? 'block' : 'none';
                });
            });

            document.querySelectorAll('.like-button').forEach(button => {
                button.addEventListener('click', () => {
                    const ideaId = button.closest('.idea').dataset.id;
                    const likeCountElement = button.querySelector('.like-count');
                    let likeCount = parseInt(likeCountElement.textContent);
                    likeCount++;
                    likeCountElement.textContent = likeCount;

                    // Envoyer la requête pour enregistrer le like en base de données
                    fetch('like.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ idea_id: ideaId })
                    });
                });
            });
        });
    </script>
</body>
</html>
