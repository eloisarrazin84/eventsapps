<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$lotId = $_GET['lot_id'];

// Récupérer les informations du lot
$stmt = $conn->prepare("SELECT * FROM lots WHERE id = :id");
$stmt->bindParam(':id', $lotId);
$stmt->execute();
$lot = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer tous les consommables
$stmt = $conn->prepare("SELECT * FROM consommables");
$stmt->execute();
$allConsommables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les consommables associés au lot
$stmt = $conn->prepare("SELECT * FROM lot_consommables WHERE lot_id = :lot_id");
$stmt->bindParam(':lot_id', $lotId);
$stmt->execute();
$associatedConsommables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mettre à jour les informations du lot et des consommables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lotName = $_POST['name'];
    $description = $_POST['description'];
    $selectedConsommables = $_POST['consommables'] ?? [];

    // Mettre à jour le lot
    $stmt = $conn->prepare("UPDATE lots SET name = :name, description = :description WHERE id = :id");
    $stmt->bindParam(':name', $lotName);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':id', $lotId);
    $stmt->execute();

    // Supprimer les consommables existants et réinsérer les nouveaux
    $stmt = $conn->prepare("DELETE FROM lot_consommables WHERE lot_id = :lot_id");
    $stmt->bindParam(':lot_id', $lotId);
    $stmt->execute();

    foreach ($selectedConsommables as $consommableId => $quantity) {
        if ($quantity > 0) {
            $stmt = $conn->prepare("INSERT INTO lot_consommables (lot_id, consommable_id, quantity) VALUES (:lot_id, :consommable_id, :quantity)");
            $stmt->bindParam(':lot_id', $lotId);
            $stmt->bindParam(':consommable_id', $consommableId);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();
        }
    }

    header("Location: /sacs/manage_lots.php");
    exit();
}
?>
