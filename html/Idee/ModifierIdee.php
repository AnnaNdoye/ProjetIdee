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

if (!isset($_GET['id'])) {
    die("ID de l'idée non spécifié.");
}

$idee_id = $_GET['id'];
$employe_id = $_SESSION['user_id'];

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.categorie_id
    FROM idee
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
    die("Idée non trouvée ou vous n'êtes pas autorisé à la modifier.");
}

$idee = $result->fetch_assoc();

$query = "SELECT id_categorie, nom_categorie FROM categorie";
$categories = $connexion->query($query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" type="tet/css" href="../../static/css/style6.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style7.css">
    <link rel="stylesheet" type="text/javascript" href="../../static/js/script2.js">
    <title>Modifier Idée</title>
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
                <h2>Modifier l'idée</h2>
            </header>

            <form action="../../database/idee/EditionAJour.php" method="post" enctype="multipart/form-data" onsubmit="syncContent()">
                <input type="hidden" name="id" value="<?php echo $idee_id; ?>">
                <div class="form-group">
                    <label for="titre">Titre:</label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($idee['titre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="contenu">Contenu:</label>
                    <div id="contenu" class="contenteditable" contenteditable="true" required><?php echo $idee['contenu_idee']; ?></div>
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
                        <input type="radio" id="publique" name="visibilite" value="publique" <?php echo $idee['est_publique'] ? 'checked' : ''; ?> required>
                        <label for="publique"><i class="fas fa-lock-open"></i> Publique</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="prive" name="visibilite" value="privé" <?php echo !$idee['est_publique'] ? 'checked' : ''; ?> required>
                        <label for="prive"><i class="fas fa-lock"></i> Privé</label>
                    </div>
                </div>

                <div class="form-group custom-select">
                    <label for="categorie">Sélectionnez une catégorie :</label>
                    <select name="categorie_id" required>
                        <option value="" disabled>Sélectionnez une catégorie</option>
                        <?php while ($row = $categories->fetch_assoc()) : ?>
                            <option value="<?php echo $row['id_categorie']; ?>" <?php echo $row['id_categorie'] == $idee['categorie_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['nom_categorie']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fichier">Choisissez un fichier :</label>
                    <input type="file" id="fichier" name="fichier" accept=".doc,.docx,.mpp,.avi,.gif,.gz,.zip,.jpeg,.jpg,.jpe,.png,.odp,.odt,.ods,.pdf,.xlsx,.pptx,.txt">
                </div>
                
                <div class="form-buttons">
                    <a href="AccueilIdee.html">Annuler</a>
                    <button type="submit">Enregistrer</button>
                </div>
            </form>
        </main>
        
        <?php
            include("../barrefooter.html");
        ?>
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

    function formatText(command) {
        document.execCommand(command, false, null);
    }

    function toggleFormat(command)
        formatText(command);
        const button = document.getElementById(`${command}Button`);
        if (document.queryCommandState(command)) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
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
<script src="../static/js/script1.js"></script>
<script src="../static/js/script2.js"></script>
</body>
</html>

<?php
$stmt->close();
$connexion->close();
?>
