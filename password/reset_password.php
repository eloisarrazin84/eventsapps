<?php
session_start();
$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->execute([':password' => $new_password, ':user_id' => $reset['user_id']]);

            $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
            $stmt->execute([':token' => $token]);

            $success = "Mot de passe réinitialisé avec succès.";
        }
    } else {
        $error = "Jeton invalide ou expiré.";
    }
} else {
    $error = "Aucun jeton fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
</head>
<body>
    <?php if ($error): ?><p><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p><?php echo $success; ?></p><?php endif; ?>
    <form method="POST" action="">
        <label>Nouveau mot de passe :</label>
        <input type="password" name="password" required>
        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
</body>
</html>
