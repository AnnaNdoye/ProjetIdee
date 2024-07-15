<?php
session_start();

// Check if session variables are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['mot_de_passe']) || !isset($_SESSION['email'])) {
    header("Location: ../Connexion.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Create database connection
$connexion = new mysqli($host, $user, $password, $database);

// Check the connection
if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

$user_id = $_SESSION['user_id'];
$mot_de_passe = $_SESSION['mot_de_passe'];
$email = $_SESSION['email'];

// Retrieve user information
$query = "SELECT is_admin, nom, prenom, poste, photo_profil, departement_id FROM employe WHERE id_employe = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $departement_id = $row['departement_id'];

    // Retrieve department name
    $departement_query = "SELECT nom_departement FROM department WHERE id_departement = ?";
    $dept_stmt = $connexion->prepare($departement_query);
    $dept_stmt->bind_param("i", $departement_id);
    $dept_stmt->execute();
    $departement_result = $dept_stmt->get_result();

    if ($departement_result->num_rows > 0) {
        $departement_row = $departement_result->fetch_assoc();
        $nom_departement = $departement_row['nom_departement'];
    } else {
        $nom_departement = "Non attribué";
    }
} else {
    echo "Aucun utilisateur trouvé.";
    exit();
}

$update_message = "";
$default_photo = "uploads/unknow.png";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_photo'])) {
        $update_query = $connexion->prepare("UPDATE employe SET photo_profil=? WHERE id_employe=?");
        $update_query->bind_param("si", $default_photo, $user_id);
        if ($update_query->execute() === TRUE) {
            $update_message = "Photo de profil supprimée.";
            header("Location: Profil.php");
            exit();
        } else {
            echo "Erreur lors de la mise à jour: " . $connexion->error;
        }
    } else {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $poste = $_POST['poste'];
        $photo_profil = $_FILES['photo_profil'];

        if ($photo_profil['name']) {
            $photo_profil_path = 'uploads/' . basename($photo_profil['name']);
            if (move_uploaded_file($photo_profil['tmp_name'], $photo_profil_path)) {
                $update_query = $connexion->prepare("UPDATE employe SET nom=?, prenom=?, poste=?, photo_profil=? WHERE id_employe=?");
                $update_query->bind_param("ssssi", $nom, $prenom, $poste, $photo_profil_path, $user_id);
            } else {
                echo "Erreur lors du téléchargement de l'image.";
                exit();
            }
        } else {
            $update_query = $connexion->prepare("UPDATE employe SET nom=?, prenom=?, poste=? WHERE id_employe=?");
            $update_query->bind_param("sssi", $nom, $prenom, $poste, $user_id);
        }

        if ($update_query->execute() === TRUE) {
            $update_message = "Mise à jour réussie.";
            header("Location: profilAdmin.php");
            exit();
        } else {
            echo "Erreur lors de la mise à jour: " . $connexion->error;
        }
    }
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/Profil.css">
    <title>Profil</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='../Accueil.html'">
            <img src="../../static/img/icon.png" alt="Logo">
            <div class="logo">
                <h1>Orange</h1>
                <h3><span class="for-ideas">for ideas</span></h3>
            </div>
        </div>
        <div class="navigation" id="zero">
            <a href="AccueilAdmin.php">Accueil</a>
        
            <a href="../Connexion.php"><i class="fas fa-user"></i> Se déconnecter</a>
        </div>
    </div>

    <div class="profile-container">
        <h2><i class="fas fa-user"></i> Mon Profil <i class="fas fa-user"></i></h2>
        <?php if ($update_message): ?>
            <div class="update-message"><?php echo $update_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="profile-photo">
                <img src="../idee/<?php echo $row['photo_profil'] ? $row['photo_profil'] : $default_photo; ?>" alt="Photo de profil" id="profile-img">
                <input type="file" id="photo_profil" name="photo_profil" style="display: none;">
                <button type="button" onclick="document.getElementById('photo_profil').click();"><i class="fas fa-pen"></i> Editer</button>
                <?php if ($row['photo_profil'] && $row['photo_profil'] !== $default_photo): ?>
                    <button type="submit" name="delete_photo"><i class="fas fa-trash"></i> Supprimer</button>
                <?php endif; ?>
            </div>
            <div class="profile-item">
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" value="<?php echo $row['nom']; ?>" required>
            </div>
            <div class="profile-item">
                <label for="prenom">Prénom:</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo $row['prenom']; ?>" required>
            </div>
            <div class="profile-item">
                <label for="email">E-mail:</label>
                <p id="email"><?php echo $email; ?></p>
            </div>
            <div class="profile-item">
                <label for="password">Mot de passe:</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="mot_de_passe" value="<?php echo $mot_de_passe; ?>" onclick="location.href='AdminChangePassword.php'" readonly>
                    <i class="fas fa-eye" id="togglePassword" style="position: absolute; top: 50%; right: 10px; cursor: pointer;"></i>
                </div>
            </div>
            <div class="profile-item">
                <label for="poste">Poste:</label>
                <input type="text" id="poste" name="poste" value="<?php echo $row['poste']; ?>" required>
            </div>
            <div class="profile-item">
                <label for="departement">Département:</label>
                <p id="departement"><?php echo $nom_departement; ?></p>
            </div>
            <div class="profile-item">
                <label for="role">Rôle :</label>
                <p id="role"><?php echo $row['is_admin'] ? "Administrateur" : "Utilisateur Simple"; ?></p>
            </div>
            <div class="profile-item" style="display: flex; justify-content: space-between;">
                <button type="submit">Mettre à jour</button>
                <button><a href="AccueilAdmin.php" style="color: white; text-decoration: none; display: block;">Quitter</a></button>
            </div>
        </form>
    </div>

    <?php
        include("../barrefooter.html");
    ?>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const profileImg = document.querySelector('#profile-img');
        const photoProfilInput = document.querySelector('#photo_profil');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        profileImg.addEventListener('click', function () {
            photoProfilInput.click();
        });

        photoProfilInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    profileImg.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>
