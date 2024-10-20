<?php
session_start();

if (isset($_POST['event_id']) && isset($_SESSION['user_id'])) {
    $eventId = $_POST['event_id'];
    $userId = $_SESSION['user_id'];

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
        $stmt->bindParam(':event_id', $eventId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // Insérer l'utilisateur dans les inscriptions
            $stmt = $conn->prepare("INSERT INTO event_user_assignments (event_id, user_id) VALUES (:event_id, :user_id)");
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            echo 'success';
        } else {
            echo 'already_registered';
        }
    } catch (PDOException $e) {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
