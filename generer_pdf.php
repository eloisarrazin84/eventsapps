<?php
require_once('tcpdf/tcpdf.php');

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['location_id'])) {
    $location_id = $_GET['location_id'];

    // Récupérer les informations de l'emplacement et des médicaments
    $stmt = $conn->prepare("SELECT location_name, bag_name FROM stock_locations WHERE id = :location_id");
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM medicaments WHERE stock_location_id = :location_id");
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();
    $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Création du PDF avec TCPDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Titre du document
    $pdf->Cell(0, 10, 'Inventaire des Médicaments - ' . htmlspecialchars($location['location_name'] . ($location['bag_name'] ? ' - ' . $location['bag_name'] : '')), 0, 1, 'C');
    $pdf->Ln(5);

    // Tableau des médicaments
    $pdf->SetFont('helvetica', '', 10);
    $html = '<table border="1" cellpadding="5">
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

    // Générer et afficher le PDF
    $pdf->Output('fiche_inventaire.pdf', 'I');
} else {
    echo "Aucun emplacement spécifié.";
}
?>
