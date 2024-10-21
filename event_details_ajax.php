<?php
$event_id = $_GET['id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de l'événement
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $event_id);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les champs supplémentaires de l'événement
    $stmt = $conn->prepare("SELECT * FROM event_fields WHERE event_id = :event_id");
    $stmt->bindParam(':event_id', $event_id);
    $stmt->execute();
    $event_fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($event) {
        echo "<h5>" . htmlspecialchars($event['event_name']) . "</h5>";
        echo "<p><strong>Date :</strong> " . htmlspecialchars($event['event_date']) . "</p>";
        echo "<p><strong>Lieu :</strong> " . htmlspecialchars($event['event_location']) . "</p>";
        echo "<p><strong>Description :</strong> " . htmlspecialchars($event['event_description']) . "</p>";

        // Formulaire d'inscription
        echo '<form id="registrationForm">';
        
        foreach ($event_fields as $field) {
            echo '<div class="form-group">';
            echo '<label>' . htmlspecialchars($field['field_name']) . '</label>';
            
            switch ($field['field_type']) {
                case 'text':
                    echo '<input type="text" class="form-control" name="fields[' . htmlspecialchars($field['field_name']) . ']">';
                    break;
                case 'number':
                    echo '<input type="number" class="form-control" name="fields[' . htmlspecialchars($field['field_name']) . ']">';
                    break;
                case 'date':
                    echo '<input type="date" class="form-control" name="fields[' . htmlspecialchars($field['field_name']) . ']">';
                    break;
                case 'checkbox':
                    echo '<input type="checkbox" name="fields[' . htmlspecialchars($field['field_name']) . ']">';
                    break;
                case 'multiple':
                    $options = explode(',', $field['field_options']);
                    echo '<select class="form-control" name="fields[' . htmlspecialchars($field['field_name']) . ']">';
                    foreach ($options as $option) {
                        echo '<option value="' . htmlspecialchars(trim($option)) . '">' . htmlspecialchars(trim($option)) . '</option>';
                    }
                    echo '</select>';
                    break;
            }

            if (!empty($field['field_description'])) {
                echo '<small class="form-text text-muted">' . htmlspecialchars($field['field_description']) . '</small>';
            }
            echo '</div>';
        }

        // Bouton d'inscription
        echo '<button type="button" class="btn btn-primary" onclick="registerForEvent(' . htmlspecialchars($event_id) . ')">S\'inscrire à cet événement</button>';
        echo '</form>';
    } else {
        echo "Événement non trouvé.";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<script>
    function registerForEvent(eventId) {
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        formData.append('event_id', eventId);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "register_event.php", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText);
            }
        };
        xhr.send(formData);
    }
</script>
