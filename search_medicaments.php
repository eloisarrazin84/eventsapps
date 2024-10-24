<?php
// Lecture du fichier texte pour récupérer les noms des médicaments
$filename = 'CIS_bdpm.txt';
$term = isset($_GET['term']) ? strtolower($_GET['term']) : '';

$suggestions = [];

if ($file = fopen($filename, 'r')) {
    while (($line = fgets($file)) !== false) {
        // Extraction du nom du médicament (ajustez selon la structure du fichier)
        $columns = explode("\t", $line);
        $medicament_name = strtolower(trim($columns[1])); // Assurez-vous que c'est la bonne colonne

        // Si le nom contient le terme recherché
        if (strpos($medicament_name, $term) !== false) {
            $suggestions[] = $medicament_name;
        }
    }
    fclose($file);
}

// Renvoi des suggestions en format JSON
header('Content-Type: application/json');
echo json_encode($suggestions);
?>
