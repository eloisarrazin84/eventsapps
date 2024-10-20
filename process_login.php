<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";  // Remplacez par votre utilisateur de base de données
$password_db = "Lipton2019!";  // Remplacez par votre mot de passe de base de données
$dbname = "outdoorsec";

$error = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];

        // Vérifier si les champs ne sont pas vides
        if (!empty($username) && !empty($password)) {
            // Préparer et exécuter la requête SQL pour trouver l'utilisateur
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Vérifier si l'utilisateur existe
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Vérifier le mot de passe
                if (password_verify($password, $user['password'])) {
                    // Enregistrer les informations de session, y compris le rôle
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Rediriger vers le tableau de bord
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Mot de passe incorrect.";
                }
            } else {
                $error = "Nom d'utilisateur incorrect.";
            }
        } else {
            $error = "Veuillez remplir tous les champs.";
        }
    }
} catch (PDOException $e) {
    $error = "Erreur de connexion : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>
<body>
    <h2>Connexion</h2>
    
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>

