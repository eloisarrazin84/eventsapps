<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour vous inscrire.";
    exit();
}

$event_id = $_POST['event_id'];
$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si l'utilisateur est déjà inscrit
    $stmt = $conn->prepare("SELECT * FROM event_user_assignments WHERE event_id = :event_id AND user_id = :user_id");
    $stmt->bindParam(':event_id', $event_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Vous êtes déjà inscrit à cet événement.";
    } else {
        // Inscription à l'événement
        $stmt = $conn->prepare("INSERT INTO event_user_assignments (event_id, user_id) VALUES (:event_id, :user_id)");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Sauvegarder les champs supplémentaires
        if (isset($_POST['fields']) && is_array($_POST['fields'])) {
            foreach ($_POST['fields'] as $field_name => $field_value) {
                $stmt = $conn->prepare("INSERT INTO user_event_data (user_id, event_id, field_name, field_value) VALUES (:user_id, :event_id, :field_name, :field_value)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':event_id', $event_id);
                $stmt->bindParam(':field_name', $field_name);
                $stmt->bindParam(':field_value', $field_value);
                $stmt->execute();
            }
        }

        echo "Inscription réussie.";
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
