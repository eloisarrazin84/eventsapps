<?php
require_once('tcpdf/tcpdf.php');

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$location_id = isset($_GET['location_id']) ? $_GET['location_id'] : null;
if (!$location_id) {
    die("Lieu de stockage non spécifié.");
}

$stmt = $conn->prepare("SELECT * FROM stock_locations WHERE id = :id");
$stmt->bindParam(':id', $location_id);
$stmt->execute();
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$location) {
    die("Lieu de stockage introuvable.");
}

$stmt = $conn->prepare("SELECT * FROM medicaments WHERE stock_location_id = :id");
$stmt->bindParam(':id', $location_id);
$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialiser le PDF
$pdf = new TCPDF();
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Ajouter le logo et le titre de manière alignée
$logo = 'https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png';
$pdf->Image($logo, 15, 10, 15, 15, 'PNG');
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Inventaire des Médicaments - " . $location['location_name'] . ' - ' . $location['bag_name'], 0, 1, 'C');
$pdf->Ln(8);

// Ajouter la date de génération alignée à droite
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Date de génération : ' . date('d/m/Y'), 0, 1, 'R');

// Tableau des médicaments
$pdf->Ln(5);
$pdf->SetFillColor(230, 230, 230); // Couleur de fond pour les en-têtes
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 8, 'Nom', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'N° de Lot', 1, 0, 'C', 1);
$pdf->Cell(20, 8, 'Quantité', 1, 0, 'C', 1);
$pdf->Cell(40, 8, 'Date d\'Expiration', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Type', 1, 1, 'C', 1);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetFillColor(255, 255, 255); // Couleur de fond pour les lignes
foreach ($medicaments as $medicament) {
    $pdf->MultiCell(60, 8, $medicament['nom'], 1, 'L', 1, 0);
    $pdf->Cell(30, 8, $medicament['numero_lot'], 1, 0, 'C', 1);
    $pdf->Cell(20, 8, $medicament['quantite'], 1, 0, 'C', 1);
    $pdf->Cell(40, 8, $medicament['date_expiration'], 1, 0, 'C', 1);
    $pdf->Cell(30, 8, $medicament['type_produit'], 1, 1, 'C', 1);
}

// Ajouter un espace pour la signature
$pdf->Ln(15);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, "Signature de la personne ayant validé l'inventaire :", 0, 1, 'L');
$pdf->Cell(0, 15, '', 'B');

// Sortie du fichier PDF
$pdf->Output('inventaire_medicaments_' . $location['location_name'] . '.pdf', 'I');
?>
