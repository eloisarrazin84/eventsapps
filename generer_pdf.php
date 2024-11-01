<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier que l'ID du lieu de stockage est passé
if (isset($_GET['location_id'])) {
    $location_id = $_GET['location_id'];

    // Récupérer les informations des médicaments pour le lieu de stockage
    $stmt = $conn->prepare("SELECT nom, numero_lot, quantite, date_expiration, type_produit FROM medicaments WHERE stock_location_id = :location_id");
    $stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $stmt->execute();
    $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Charger TCPDF
    require_once('tcpdf/tcpdf.php');
    $pdf = new TCPDF();
    $pdf->AddPage();

    // Titre et détails du lieu de stockage
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Inventaire des Médicaments', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Lieu de stockage : ' . $location_name, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Date de génération : ' . date('d/m/Y'), 0, 1, 'C');

    // Logo
    $pdf->Image('https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png', 10, 10, 20);

    // Si des médicaments sont trouvés
    if ($medicaments) {
        // En-tête du tableau
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(40, 10, 'Nom', 1, 0, 'C', 1);
        $pdf->Cell(30, 10, 'N° de Lot', 1, 0, 'C', 1);
        $pdf->Cell(20, 10, 'Quantité', 1, 0, 'C', 1);
        $pdf->Cell(30, 10, 'Date d\'Expiration', 1, 0, 'C', 1);
        $pdf->Cell(20, 10, 'Type', 1, 1, 'C', 1);

        // Remplissage des lignes de données
        $pdf->SetFont('helvetica', '', 10);
        foreach ($medicaments as $medicament) {
            $pdf->Cell(40, 10, $medicament['nom'], 1);
            $pdf->Cell(30, 10, $medicament['numero_lot'], 1);
            $pdf->Cell(20, 10, $medicament['quantite'], 1);
            $pdf->Cell(30, 10, $medicament['date_expiration'], 1);
            $pdf->Cell(20, 10, $medicament['type_produit'], 1, 1);
        }
    } else {
        // Message si aucun médicament n'est trouvé
        $pdf->Cell(0, 10, 'Aucun médicament trouvé pour ce lieu de stockage.', 0, 1, 'C');
    }

    // Signature
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Signature de la personne ayant validé l\'inventaire :', 0, 1, 'L');
    $pdf->Ln(20);
    $pdf->Cell(0, 0, '', 'T'); // Ligne de signature

    $pdf->Output('inventaire.pdf', 'I');
} else {
    echo "Erreur : Aucun lieu de stockage spécifié.";
}
?>
