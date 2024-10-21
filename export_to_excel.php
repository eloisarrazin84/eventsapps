<?php
require 'vendor/autoload.php'; // Inclure PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

if (!isset($_GET['event_id'])) {
    die("Aucun événement spécifié.");
}

$eventId = $_GET['event_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les inscriptions avec les champs récents
    $stmt = $conn->prepare("
        SELECT u.username, ued.field_name, MAX(ued.field_value) AS field_value
        FROM users u
        JOIN event_user_assignments eua ON u.id = eua.user_id
        JOIN user_event_data ued ON u.id = ued.user_id
        WHERE eua.event_id = :event_id AND ued.event_id = :event_id
        GROUP BY u.username, ued.field_name
        ORDER BY u.username ASC
    ");
    $stmt->bindParam(':event_id', $eventId);
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Créer une nouvelle feuille Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Inscriptions");

    // Ajouter l'en-tête
    $sheet->setCellValue('A1', 'Nom d\'utilisateur');
    $sheet->setCellValue('B1', 'Champ');
    $sheet->setCellValue('C1', 'Réponse');

    // Remplir le fichier avec les données
    $row = 2;
    foreach ($registrations as $registration) {
        $sheet->setCellValue('A' . $row, $registration['username']);
        $sheet->setCellValue('B' . $row, $registration['field_name']);
        $sheet->setCellValue('C' . $row, $registration['field_value']);
        $row++;
    }

    // Exporter vers Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="inscriptions.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
