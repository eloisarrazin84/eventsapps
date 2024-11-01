<?php
require_once('tcpdf/tcpdf.php');

// Récupérer les données de la requête
$locationId = $_POST['location_id'];
$signatureBase64 = $_POST['signature'];

// Décoder la signature si elle est fournie
if ($signatureBase64) {
    $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
    file_put_contents('signature.png', $signatureData);
}

// Configuration du PDF
$pdf = new TCPDF();
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Logo et titre
$pdf->Image('https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png', 15, 10, 30, '', 'PNG');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 20, 'Inventaire des Médicaments', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, "Lieu de stockage : {$locationName}", 0, 1, 'C');
$pdf->Cell(0, 10, 'Date de génération : ' . date('d/m/Y'), 0, 1, 'R');

// Tableau des médicaments
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0, 0, 0);

$pdf->Cell(60, 8, 'Nom', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'N° de Lot', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Quantité', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Date d\'Expiration', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Type', 1, 1, 'C', true);

// Remplir les lignes du tableau
foreach ($medicaments as $medicament) {
    $pdf->Cell(60, 8, $medicament['nom'], 1);
    $pdf->Cell(40, 8, $medicament['numero_lot'], 1);
    $pdf->Cell(20, 8, $medicament['quantite'], 1);
    $pdf->Cell(40, 8, $medicament['date_expiration'], 1);
    $pdf->Cell(30, 8, $medicament['type_produit'], 1);
    $pdf->Ln();
}

// Ajouter la signature si disponible
if (file_exists('signature.png')) {
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Signature de la personne ayant validé l\'inventaire :', 0, 1);
    $pdf->Image('signature.png', 15, $pdf->GetY(), 50, '', 'PNG');
    unlink('signature.png');  // Supprimer l'image après l'insertion
}

// Sortie du PDF
$pdf->Output('inventaire.pdf', 'I');
?>
