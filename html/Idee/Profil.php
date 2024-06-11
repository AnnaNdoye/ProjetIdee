<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mot_de_passe']) || !isset($_SESSION['email'])) 
{
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

$user_id = $_SESSION['user_id'];
$mot_de_passe = $_SESSION['mot_de_passe'];
$email = $_SESSION['email'];

$query = "SELECT nom, prenom, poste, photo_profil, departement_id FROM employe WHERE id_employe = $user_id";
$result = $connexion->query($query);

if ($result->num_rows > 0) 
{
    $row = $result->fetch_assoc();
    $departement_id = $row['departement_id'];

    $departement_query = "SELECT nom_departement FROM department WHERE id_departement = $departement_id";
    $departement_result = $connexion->query($departement_query);

    if ($departement_result->num_rows > 0) {
        $departement_row = $departement_result->fetch_assoc();
        $nom_departement = $departement_row['nom_departement'];
    } 
    else 
    {
        $nom_departement = "Non attribué";
    }
} 
else 
{
    echo "Aucun utilisateur trouvé.";
}

$update_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $poste = $_POST['poste'];
    $photo_profil = $_FILES['photo_profil'];

    // téléchargement de l'image
    $photo_profil_path = $row['photo_profil'];
    if ($photo_profil['name']) 
    {
        $target_dir = __DIR__ . "/uploads/"; //on définit le chemin vers l'image
        if (!is_dir($target_dir))  //on vérifie si le dossier qui doit contenir l'image exste
        {
            mkdir($target_dir, 0755, true); // on le crée si ce n'est pas le cas et on accorde des privilèges sur ce dossier
        }
        $target_file = $target_dir . basename($photo_profil["name"]);

        if (move_uploaded_file($photo_profil["tmp_name"], $target_file)) 
        {
            $photo_profil_path = "uploads/" . basename($photo_profil["name"]);
        } 
        else 
        {
            echo "Erreur lors du téléchargement de la photo.";
        }
    }

    $update_query = "UPDATE employe SET nom='$nom', prenom='$prenom', poste='$poste', photo_profil='$photo_profil_path' WHERE id_employe=$user_id";

    if ($connexion->query($update_query) === TRUE) 
    {
        $update_message = "Mise à jour réussie.";
        header("Location: Profil.php");
        exit();
    } 
    else 
    {
        echo "Erreur lors de la mise à jour: " . $connexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/Profil.css">
    <title>Profil</title>
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-item {
            margin-bottom: 20px;
        }

        .profile-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .profile-item p, .profile-item input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }

        .profile-item button {
            width: 48%;
            padding: 10px;
            background-color: #ff5722;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .profile-item button:hover {
            background-color: #e64a19;
        }

        .header .navigation a {
            margin-left: 20px;
            color: #ff6600;
            text-decoration: none;
            transition: color 0.3s;
        }

        .header .navigation a:hover {
            color: #000;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .profile-photo {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .profile-photo img:hover {
            transform: scale(1.2);
        }

        .update-message {
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .header {
            background-color: white;
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
            <a href="AccueilIdee.php">Accueil</a>
        </div>
        <div class="navigation">
            <a href="../Connexion.php"><i class="fas fa-user"></i> Se déconnecter</a>
        </div>
    </div>

    <div class="profile-container">
        <h2>Mon Profil</h2>
        <?php if ($update_message): ?>
            <div class="update-message"><?php echo $update_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="profile-photo">
                <?php if ($row['photo_profil']): ?>
                    <img src="<?php echo $row['photo_profil']; ?>" alt="Photo de profil" id="profile-img">
                <?php else: ?>
                    <img src="uploads/unknow.png" alt="Photo par défaut" id="profile-img">
                <?php endif; ?>
                <input type="file" id="photo_profil" name="photo_profil" style="display: none;">
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
                    <input type="password" id="password" name="mot_de_passe" value="<?php echo $mot_de_passe; ?>" onclick="location.href='ChangePassword2.html'" required>
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
            <div class="profile-item" style="display: flex; justify-content: space-between;">
                <button type="submit">Mettre à jour</button>
                <button><a href="AccueilIdee.php" style="color: white; text-decoration: none;">Quitter</a></button>
            </div>
        </form>
    </div>

    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

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
