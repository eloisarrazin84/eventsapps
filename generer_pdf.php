<?php
require_once('tcpdf/tcpdf.php');

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si le lieu de stockage est spécifié
$location_id = isset($_POST['location_id']) ? $_POST['location_id'] : null;
$signatureData = isset($_POST['signature']) ? $_POST['signature'] : null;

if (!$location_id) {
    die("Lieu de stockage non spécifié.");
}

// Récupérer les informations du lieu de stockage
$stmt = $conn->prepare("SELECT * FROM stock_locations WHERE id = :id");
$stmt->bindParam(':id', $location_id);
$stmt->execute();
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$location) {
    die("Lieu de stockage introuvable.");
}

// Récupérer les médicaments pour le lieu de stockage spécifié
$stmt = $conn->prepare("SELECT * FROM medicaments WHERE stock_location_id = :id");
$stmt->bindParam(':id', $location_id);
$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialiser le PDF
$pdf = new TCPDF();
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// Ajouter le logo et les informations du titre
$logo = 'https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png';
$pdf->Image($logo, 15, 10, 20, 20, 'PNG');
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, "Inventaire des Médicaments", 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $location['location_name'] . ' - ' . $location['bag_name'], 0, 1, 'C');
$pdf->Ln(5);

// Date de génération
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 0, 'Date de génération : ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Ln(8);

// En-tête du tableau
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(230, 230, 250);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(150, 150, 150);

$pdf->Cell(70, 8, 'Nom', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'N° de Lot', 1, 0, 'C', 1);
$pdf->Cell(20, 8, 'Quantité', 1, 0, 'C', 1);
$pdf->Cell(40, 8, 'Date d\'Expiration', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Type', 1, 1, 'C', 1);

// Contenu du tableau
$pdf->SetFont('helvetica', '', 9);
$pdf->SetFillColor(245, 245, 245);
$fill = 0;

foreach ($medicaments as $medicament) {
    $pdf->MultiCell(70, 8, $medicament['nom'], 1, 'L', $fill, 0, '', '', true, 0, false, true, 8, 'M');
    $pdf->Cell(30, 8, $medicament['numero_lot'], 1, 0, 'C', $fill);
    $pdf->Cell(20, 8, $medicament['quantite'], 1, 0, 'C', $fill);
    $pdf->Cell(40, 8, $medicament['date_expiration'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 8, $medicament['type_produit'], 1, 1, 'C', $fill);
    $fill = !$fill;
}

// Section de signature
$pdf->Ln(15);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, "Signature de la personne ayant validé l'inventaire :", 0, 1, 'L');
$pdf->Cell(0, 15, '', 'B');

// Si une signature est fournie, l'ajouter au PDF
if ($signatureData) {
    $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
    $signatureData = base64_decode($signatureData);
    $signatureFilePath = '/tmp/signature.png';
    file_put_contents($signatureFilePath, $signatureData);

    // Ajouter la signature au PDF
    $pdf->Image($signatureFilePath, 15, $pdf->GetY() + 5, 40, 20, 'PNG');
}

// Sortie du PDF
$pdf->Output('inventaire_medicaments_' . $location['location_name'] . '.pdf', 'I');
?>
