<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $locationName = htmlspecialchars($_POST['location_name']);
    $bagName = isset($_POST['bag_name']) ? htmlspecialchars($_POST['bag_name']) : null;

    if (isset($_POST['add_location'])) {
        $stmt = $conn->prepare("INSERT INTO stock_locations (location_name, bag_name) VALUES (:location_name, :bag_name)");
        $stmt->bindParam(':location_name', $locationName);
        $stmt->bindParam(':bag_name', $bagName);
        $stmt->execute();
    } elseif (isset($_POST['delete_location'])) {
        $locationId = $_POST['location_id'];
        $stmt = $conn->prepare("DELETE FROM stock_locations WHERE id = :location_id");
        $stmt->bindParam(':location_id', $locationId);
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT * FROM stock_locations");
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Lieux de Stockage</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: bold;
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-inline .form-control {
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-right: 10px;
        }
        .btn {
            border-radius: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-primary, .btn-danger, .btn-secondary {
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Bouton de retour au tableau de bord -->
    <a href="dashboard_medicaments.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Retour</a>

    <h2>Gestion des Lieux de Stockage</h2>

    <!-- Formulaire pour ajouter un lieu de stockage -->
    <form method="POST" class="form-inline justify-content-center mb-4">
        <input type="text" name="location_name" class="form-control mr-2" placeholder="Nom du lieu de stockage" required>
        <input type="text" name="bag_name" class="form-control mr-2" placeholder="Nom du sac (facultatif)">
        <button type="submit" name="add_location" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Ajouter</button>
    </form>

    <!-- Tableau des lieux de stockage -->
    <table class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Lieu de stockage</th>
                <th>Sac</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $location): ?>
                <tr>
                    <td><?php echo htmlspecialchars($location['location_name']); ?></td>
                    <td><?php echo htmlspecialchars($location['bag_name']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                            <button type="submit" name="delete_location" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu de stockage ?');">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap et Font Awesome -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
