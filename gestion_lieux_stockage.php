<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Traitement des requêtes POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_location'])) {
        $locationName = htmlspecialchars($_POST['location_name']);
        $bagName = isset($_POST['bag_name']) ? htmlspecialchars($_POST['bag_name']) : null;

        $stmt = $conn->prepare("INSERT INTO stock_locations (location_name, bag_name) VALUES (:location_name, :bag_name)");
        $stmt->bindParam(':location_name', $locationName);
        $stmt->bindParam(':bag_name', $bagName);
        $stmt->execute();

    } elseif (isset($_POST['delete_location'])) {
        $locationId = $_POST['location_id'];
        $stmt = $conn->prepare("DELETE FROM stock_locations WHERE id = :location_id");
        $stmt->bindParam(':location_id', $locationId);
        $stmt->execute();
        
    } elseif (isset($_POST['edit_location'])) {
        $locationId = $_POST['location_id'];
        $locationName = htmlspecialchars($_POST['location_name']);
        $bagName = isset($_POST['bag_name']) ? htmlspecialchars($_POST['bag_name']) : null;

        $stmt = $conn->prepare("UPDATE stock_locations SET location_name = :location_name, bag_name = :bag_name WHERE id = :location_id");
        $stmt->bindParam(':location_name', $locationName);
        $stmt->bindParam(':bag_name', $bagName);
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
        /* Vos styles ici */
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard_medicaments.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Retour</a>
    <h2>Gestion des Lieux de Stockage</h2>

    <form method="POST" class="form-inline justify-content-center mb-4">
        <input type="text" name="location_name" class="form-control mr-2" placeholder="Nom du lieu de stockage" required>
        <input type="text" name="bag_name" class="form-control mr-2" placeholder="Nom du sac (facultatif)">
        <button type="submit" name="add_location" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Ajouter</button>
    </form>

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
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editModal<?php echo $location['id']; ?>">
                            <i class="fas fa-edit"></i> Modifier
                        </button>

                        <!-- Modal pour modifier un lieu -->
                        <div class="modal fade" id="editModal<?php echo $location['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Modifier le lieu de stockage</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                                            <div class="form-group">
                                                <label for="location_name">Nom du lieu</label>
                                                <input type="text" name="location_name" class="form-control" value="<?php echo htmlspecialchars($location['location_name']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="bag_name">Nom du sac</label>
                                                <input type="text" name="bag_name" class="form-control" value="<?php echo htmlspecialchars($location['bag_name']); ?>">
                                            </div>
                                            <button type="submit" name="edit_location" class="btn btn-primary">Modifier</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
