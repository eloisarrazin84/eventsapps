<?php
require_once('tcpdf/tcpdf.php'); // Inclure TCPDF

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si un lieu de stockage a été sélectionné
if (isset($_GET['location_id'])) {
    $locationId = $_GET['location_id'];

    // Récupérer les informations du lieu de stockage
    $stmt = $conn->prepare("SELECT * FROM stock_locations WHERE id = :id");
    $stmt->bindParam(':id', $locationId);
    $stmt->execute();
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les médicaments pour le lieu de stockage sélectionné
    $stmt = $conn->prepare("SELECT * FROM medicaments WHERE stock_location_id = :location_id");
    $stmt->bindParam(':location_id', $locationId);
    $stmt->execute();
    $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Créer une instance de TCPDF
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Outdoor Secours');
    $pdf->SetTitle('Inventaire des Médicaments');
    $pdf->AddPage();

    // Titre
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Inventaire des Médicaments - ' . htmlspecialchars($location['location_name']), 0, 1, 'C');

    // Tableau des médicaments
    $pdf->SetFont('helvetica', '', 12);
    $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr style="font-weight: bold;">
                        <th>Nom</th>
                        <th>Description</th>
                        <th>N° de lot</th>
                        <th>Quantité</th>
                        <th>Date d\'expiration</th>
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

    // Ajouter le tableau au PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Sortie du PDF
    $pdf->Output('inventaire_' . $location['location_name'] . '.pdf', 'I');
} else {
    echo "Lieu de stockage non spécifié.";
}
