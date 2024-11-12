<?php
session_start();
$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Connexion à la base de données
    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifiez si le token est valide et non expiré
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
        $stmt->execute([':token' => $token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reset) {
            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

                // Mise à jour du mot de passe dans la table `users`
                $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                $stmt->execute([':password' => $new_password, ':user_id' => $reset['user_id']]);

                // Suppression du token utilisé
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
                $stmt->execute([':token' => $token]);

                $success = "Votre mot de passe a été réinitialisé avec succès.";
            }
        } else {
            $error = "Jeton invalide ou expiré.";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion à la base de données : " . $e->getMessage();
    }
} else {
    $error = "Aucun jeton de réinitialisation fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        h2 {
            color: #007bff;
        }
        .alert {
            font-size: 1.1em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Réinitialiser le mot de passe</h2>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <p>Veuillez entrer votre nouveau mot de passe.</p>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe :</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
