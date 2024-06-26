<?php
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style6.css">
    <link rel="stylesheet" href="../../static/css/style7.css">
    <title>Nouvelle Idée</title>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo" onclick="location.href='../accueil.html'">
                <img src="../../static/img/icon.png" alt="Logo">
                <h1>Orange</h1>
                <h3><span class="for-ideas">for ideas</span></h3>
            </div>
            <strong>
                <nav class="navigation">
                    <a href="AccueilIdee.php">Accueil</a>
                    <a href="IdeePublique.php">Idées Publiques</a>
                </nav>
            </strong>
            <div class="connect_entete">
                <a href="../Connexion.php"><i class="fas fa-user"></i> Se déconnecter</a>
            </div>
            
            <div class="connect_entete">
                <a href="Profil.php"><i class="fas fa-user-circle"></i> Profil</a>
            </div>
        </header>
        
        <main class="main-content">
            <header class="entete">
                <h2>Créer une nouvelle idée</h2>
            </header>

            <?php
                $host = "localhost";
                $user = "root";
                $password = "";
                $database = "idee";
                $connection = mysqli_connect($host, $user, $password, $database);

                if ($connection->connect_error) {
                    die("Erreur de connexion à la base de données : " . $connection->connect_error);
                }

                //réupérer l'id de l'utilisateur
                $id_utilisateur = $_SESSION['user_id'];

                // Récupérer les catégories
                $query = "SELECT id_categorie, nom_categorie FROM categorie";
                $result = mysqli_query($connection, $query);
                if (!$result) 
                {
                    die("Erreur lors de la requête : " . mysqli_error($connection));
                }
            ?>
            
            <form action="../../database/idee/soumettreidee.php" method="post" enctype="multipart/form-data" onsubmit="syncContent()">
                <div class="form-group">
                    <label for="titre">Titre:</label>
                    <input type="text" id="titre" name="titre" required>
                </div>

                <div class="form-group">
                    <label for="contenu">Contenu:</label>
                    <div id="contenu" class="contenteditable" contenteditable="true" required></div>
                    <textarea id="hiddenContent" name="contenu" style="display:none;"></textarea>
                    <div class="toolbar">
                        <button type="button" id="boldButton" onclick="toggleFormat('bold')"><i class="fas fa-bold"></i></button>
                        <button type="button" id="italicButton" onclick="toggleFormat('italic')"><i class="fas fa-italic"></i></button>
                        <button type="button" id="underlineButton" onclick="toggleFormat('underline')"><i class="fas fa-underline"></i></button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="visibilite">Choisissez la visibilité de votre idée :</label>
                    <div class="radio-group">
                        <input type="radio" id="publique" name="visibilite" value="publique" required>
                        <label for="publique"><i class="fas fa-lock-open"></i> Publique</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="prive" name="visibilite" value="privé" required>
                        <label for="prive"><i class="fas fa-lock"></i> Privé</label>
                    </div>
                </div>

                <div class="form-group" class="custom-select">
                    <label for="contenu">Sélectionnez une catégorie :</label>
                    <select name="categorie_id" required>
                    <option value="" disabled selected>Sélectionnez une catégorie </option>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) 
                    {
                        echo '<option value="' . $row['id_categorie'] . '">' . $row['nom_categorie'] . '</option>';
                    }
                    ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contenu">Choississez un fichier :</label>
                    <input type="file" id="fichier" name="fichier" accept=".doc,.docx,.mpp,.avi,.gif,.gz,.zip,.jpeg,.jpg,.jpe,.png,.odp,.odt,.ods,.pdf,.xlsx,.pptx,.txt">
                </div>
                
                <div class="form-buttons">
                    <a href="AccueilIdee.php">Annuler</a>
                    <button type="submit">Créer</button>
                </div>
            </form>
        </main>
        
        <footer class="footer">
            <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
            <h4 class="footer-right">©Orange/Juin2024</h4>
        </footer>
    </div>
    <div id="alert-container"></div>
<script>
    function displayAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        alert.textContent = message;
        alertContainer.appendChild(alert);
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }
</script>
    <script src="../static/js/script1.js"></script>
    <script src="../static/js/script2.js"></script>
    <script>
        function formatText(command) {
            document.execCommand(command, false, null);
        }

        function toggleFormat(command) {
            formatText(command);
            const button = document.getElementById(`${command}Button`);
            if (document.queryCommandState(command)) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        }

        function syncContent() {
            const contentEditableDiv = document.getElementById('contenu');
            const hiddenTextarea = document.getElementById('hiddenContent');
            hiddenTextarea.value = contentEditableDiv.innerHTML;
        }

        function displayFileName() {
            const input = document.getElementById('fichier');
            const fileName = input.files[0].name;
            document.getElementById('file-name').textContent = `Selected file: ${fileName}`;
        }

        document.getElementById('fichier').addEventListener('change', displayFileName);
    </script>
</body>
</html>
