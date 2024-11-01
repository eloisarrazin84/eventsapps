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

// Initialiser le PDF avec des marges réduites pour un design épuré
$pdf = new TCPDF();
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// Couleurs de thème
$headerColor = array(230, 230, 250);
$borderColor = array(150, 150, 150);
$fillColor = array(245, 245, 245);

// Ajouter le logo et le titre avec une mise en page moderne
$logo = 'https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png';
$pdf->Image($logo, 15, 10, 20, 20, 'PNG');
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, "Inventaire des Médicaments", 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $location['location_name'] . ' - ' . $location['bag_name'], 0, 1, 'C');
$pdf->Ln(5);

// Ajouter la date de génération avec une police plus petite
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 0, 'Date de génération : ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Ln(8);

// En-tête de tableau sans bordures supérieures et inférieures
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);

$pdf->Cell(70, 8, 'Nom', 'LTR', 0, 'C', 1);
$pdf->Cell(30, 8, 'N° de Lot', 'LTR', 0, 'C', 1);
$pdf->Cell(20, 8, 'Quantité', 'LTR', 0, 'C', 1);
$pdf->Cell(40, 8, 'Date d\'Expiration', 'LTR', 0, 'C', 1);
$pdf->Cell(30, 8, 'Type', 'LTR', 1, 'C', 1);

// Lignes du tableau
$pdf->SetFont('helvetica', '', 9);
$pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
$pdf->SetTextColor(0, 0, 0);
$fill = 0;

foreach ($medicaments as $medicament) {
    $pdf->MultiCell(70, 8, $medicament['nom'], 'LR', 'L', $fill, 0, '', '', true, 0, false, true, 8, 'M');
    $pdf->Cell(30, 8, $medicament['numero_lot'], 'LR', 0, 'C', $fill);
    $pdf->Cell(20, 8, $medicament['quantite'], 'LR', 0, 'C', $fill);
    $pdf->Cell(40, 8, $medicament['date_expiration'], 'LR', 0, 'C', $fill);
    $pdf->Cell(30, 8, $medicament['type_produit'], 'LR', 1, 'C', $fill);
    $fill = !$fill; // Alterne la couleur de fond pour chaque ligne
}

// Ajouter la section de signature avec un peu d'espace
$pdf->Ln(15);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, "Signature de la personne ayant validé l'inventaire :", 0, 1, 'L');
$pdf->Cell(0, 15, '', 'B');

// Sortie du fichier PDF
$pdf->Output('inventaire_medicaments_' . $location['location_name'] . '.pdf', 'I');
?>
