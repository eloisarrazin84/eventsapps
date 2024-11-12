<?php
session_start();
require_once __DIR__ . '/../EmailService.php'; // Charger le service d'email

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);

    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $stmt->execute([':user_id' => $user['id'], ':token' => $token, ':expires_at' => $expires_at]);

        $emailService = new EmailService();
        $resetLink = "https://event.outdoorsecours.fr/password/reset_password.php?token=$token";
        $emailBody = "<p>Bonjour,</p><p>Cliquez sur le lien pour réinitialiser votre mot de passe : <a href='$resetLink'>$resetLink</a></p>";
        $emailService->sendEmail($email, "Réinitialisation de votre mot de passe", $emailBody);

        $success = "Un email de réinitialisation a été envoyé.";
    } else {
        $error = "Aucun utilisateur trouvé avec cet email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
</head>
<body>
    <?php if ($error): ?><p><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p><?php echo $success; ?></p><?php endif; ?>
    <form method="POST" action="">
        <label>Email :</label>
        <input type="email" name="email" required>
        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
</body>
</html>
