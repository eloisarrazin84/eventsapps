<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les champs supplémentaires pour l'événement
        $stmt = $conn->prepare("SELECT field_name, field_type, field_options, field_description FROM event_fields WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Générer le formulaire avec les champs supplémentaires
        foreach ($fields as $field) {
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

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Aucun événement sélectionné.";
}
?>
