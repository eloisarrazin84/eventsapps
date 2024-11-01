<?php
require_once('tcpdf/tcpdf.php');

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$location_id = $_POST['location_id'];
$signature = $_POST['signature'];

// Récupération des données de stockage et des médicaments
$stmt = $conn->prepare("SELECT * FROM stock_locations WHERE id = :id");
$stmt->bindParam(':id', $location_id);
$stmt->execute();
$location = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM medicaments WHERE stock_location_id = :id");
$stmt->bindParam(':id', $location_id);
$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création du PDF
$pdf = new TCPDF();
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Ajouter le logo et le titre
$logo = 'https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png';
$pdf->Image($logo, 15, 10, 20, 20, 'PNG');
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, "Inventaire des Médicaments", 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $location['location_name'] . ' - ' . $location['bag_name'], 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Date de génération : ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Ln(8);

// Génération du tableau d'inventaire
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(70, 8, 'Nom', 'LTR', 0, 'C', 1);
$pdf->Cell(30, 8, 'N° de Lot', 'LTR', 0, 'C', 1);
$pdf->Cell(20, 8, 'Quantité', 'LTR', 0, 'C', 1);
$pdf->Cell(40, 8, 'Date d\'Expiration', 'LTR', 0, 'C', 1);
$pdf->Cell(30, 8, 'Type', 'LTR', 1, 'C', 1);

$pdf->SetFont('helvetica', '', 9);
$fill = 0;
foreach ($medicaments as $medicament) {
    $pdf->Cell(70, 8, $medicament['nom'], 'LR', 0, 'L', $fill);
    $pdf->Cell(30, 8, $medicament['numero_lot'], 'LR', 0, 'C', $fill);
    $pdf->Cell(20, 8, $medicament['quantite'], 'LR', 0, 'C', $fill);
    $pdf->Cell(40, 8, $medicament['date_expiration'], 'LR', 0, 'C', $fill);
    $pdf->Cell(30, 8, $medicament['type_produit'], 'LR', 1, 'C', $fill);
    $fill = !$fill;
}

// Ajouter la signature si elle existe
if ($signature) {
    $signaturePath = 'temp_signature.png';
    file_put_contents($signaturePath, base64_decode(explode(',', $signature)[1]));
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, "Signature de la personne ayant validé l'inventaire :", 0, 1, 'L');
    $pdf->Image($signaturePath, 15, $pdf->GetY(), 50);
    unlink($signaturePath); // Supprimer l'image temporaire après utilisation
} else {
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, "Signature de la personne ayant validé l'inventaire : ", 0, 1, 'L');
    $pdf->Ln(10);
}

// Sortie du PDF
$pdf->Output('inventaire_medicaments_' . $location['location_name'] . '.pdf', 'I');
?>
