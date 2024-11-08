<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_GET['bag_id'])) {
    echo "ID de sac non spécifié.";
    exit();
}

$bagId = $_GET['bag_id'];

// Récupérer les informations du sac
$stmt = $conn->prepare("SELECT bags.*, stock_locations.location_name, stock_locations.bag_name 
                        FROM bags 
                        JOIN stock_locations ON bags.location_id = stock_locations.id 
                        WHERE bags.id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$bag = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les lots associés au sac
$stmt = $conn->prepare("SELECT lots.id, lots.name 
                        FROM lots 
                        JOIN bag_lots ON lots.id = bag_lots.lot_id 
                        WHERE bag_lots.bag_id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    $event = $_POST['event'];
    $medicName = $_POST['medic_name'];
    $returnDate = $_POST['return_date'];
    $inspectorName = $_POST['inspector_name'];
    $inventoryResults = [];
    $pdfContent = "";

    foreach ($lots as $lot) {
        $lotId = $lot['id'];
        $rehassortDone = isset($_POST["rehassort_$lotId"]) ? "Oui" : "Non";
        $comment = $_POST["comment_$lotId"] ?? '';

        $inventoryResults[] = [
            'lot_name' => $lot['name'],
            'rehassort' => $rehassortDone,
            'comment' => $comment,
        ];
        $pdfContent .= "Lot: " . $lot['name'] . " - Réassort fait: $rehassortDone - Commentaire: $comment\n";
    }

    // Générer le PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Procédure de Retour du Sac');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);

    $pdf->Cell(0, 10, "Nom du sac : " . $bag['name'], 0, 1);
    $pdf->Cell(0, 10, "Lieu de stockage : " . $bag['location_name'] . " - " . $bag['bag_name'], 0, 1);
    $pdf->Cell(0, 10, "Événement : " . $event, 0, 1);
    $pdf->Cell(0, 10, "Médecin/infirmer sur place : " . $medicName, 0, 1);
    $pdf->Cell(0, 10, "Date de retour : " . $returnDate, 0, 1);
    $pdf->Cell(0, 10, "Inspecteur : " . $inspectorName, 0, 1);
    $pdf->Ln(10);
    
    $pdf->Cell(0, 10, 'Lots dans le Sac', 0, 1);
    foreach ($inventoryResults as $result) {
        $pdf->Cell(0, 10, "Lot: " . $result['lot_name'] . " - Réassort: " . $result['rehassort'] . " - Commentaire: " . $result['comment'], 0, 1);
    }
    
    // Enregistrer le PDF
    $pdfDir = __DIR__ . '/../uploads/documents/';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }
    $pdfFileName = $pdfDir . 'return_' . $bagId . '_' . date('Ymd_His') . '.pdf';
    $pdf->Output('F', $pdfFileName);

    // Sauvegarder le chemin d'accès du PDF dans la base de données
    $stmt = $conn->prepare("INSERT INTO documents (bag_id, document_path, document_date) VALUES (:bag_id, :document_path, NOW())");
    $stmt->execute([
        ':bag_id' => $bagId,
        ':document_path' => $pdfFileName
    ]);

    header("Location: /sacs/bag_tracking.php?bag_id=" . $bagId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Procédure de Retour du Sac</title>
</head>
<body>
<div class="container mt-5">
    <h2>Procédure de Retour du Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <form method="POST">
        <div class="form-group">
            <label for="event">Événement où était le sac :</label>
            <input type="text" name="event" id="event" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="medic_name">Nom du médecin ou infirmier sur place :</label>
            <input type="text" name="medic_name" id="medic_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="return_date">Date de retour :</label>
            <input type="date" name="return_date" id="return_date" class="form-control" required>
        </div>
        <hr>
        <h4>Lots présents</h4>
        <?php foreach ($lots as $lot): ?>
            <div class="form-group">
                <label><?php echo htmlspecialchars($lot['name']); ?></label>
                <input type="checkbox" name="rehassort_<?php echo $lot['id']; ?>"> Réassort fait
                <input type="text" name="comment_<?php echo $lot['id']; ?>" class="form-control" placeholder="Commentaire">
            </div>
        <?php endforeach; ?>
        <div class="form-group">
            <label for="inspector_name">Nom et Prénom de la personne qui fait l'inventaire :</label>
            <input type="text" name="inspector_name" id="inspector_name" class="form-control" required>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="seal_check" required>
            <label class="form-check-label" for="seal_check">Avez-vous mis les scellés sur le sac ?</label>
        </div>
        <button type="submit" class="btn btn-primary" name="submit_return">Valider la Procédure de Retour</button>
    </form>
</div>
</body>
</html>
