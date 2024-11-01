<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('tcpdf/tcpdf.php'); // Chemin vers la bibliothèque TCPDF

// Vérifier si un ID de lieu est passé en paramètre
if (!isset($_GET['location_id'])) {
    die('Lieu de stockage non spécifié.');
}

$locationId = $_GET['location_id'];

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations du lieu de stockage
    $stmt = $conn->prepare("SELECT location_name, bag_name FROM stock_locations WHERE id = :id");
    $stmt->bindParam(':id', $locationId, PDO::PARAM_INT);
    $stmt->execute();
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$location) {
        die('Lieu de stockage non trouvé.');
    }

    // Récupérer les médicaments pour le lieu de stockage spécifié
    $stmt = $conn->prepare("SELECT nom, description, numero_lot, quantite, date_expiration, type_produit FROM medicaments WHERE stock_location_id = :location_id");
    $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
    $stmt->execute();
    $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Création du PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Outdoor Secours');
$pdf->SetTitle('Inventaire des Médicaments');
$pdf->SetSubject('Inventaire par lieu de stockage');
$pdf->SetHeaderData('', 0, 'Inventaire des Médicaments', $location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : ''));

// Configurer les marges et le pied de page
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Ajouter une page
$pdf->AddPage();

// Titre
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Inventaire des Médicaments - ' . $location['location_name'], 0, 1, 'C');
$pdf->Ln(5);

// Tableau des médicaments
$pdf->SetFont('helvetica', '', 10);
$html = '<table border="1" cellpadding="4">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>N° de Lot</th>
                    <th>Quantité</th>
                    <th>Date d\'Expiration</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>';

foreach ($medicaments as $medicament) {
    $html .= '<tr>
                <td>' . htmlspecialchars($medicament['nom']) . '</td>
                <td>' . htmlspecialchars($medicament['description']) . '</td>
                <td>' . htmlspecialchars($medicament['numero_lot']) . '</td>
                <td>' . htmlspecialchars($medicament['quantite']) . '</td>
                <td>' . htmlspecialchars($medicament['date_expiration']) . '</td>
                <td>' . htmlspecialchars($medicament['type_produit']) . '</td>
              </tr>';
}

$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Sortie du PDF
$pdf->Output('inventaire_medicaments.pdf', 'I');
?>
