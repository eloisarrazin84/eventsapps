<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les valeurs des filtres
$filterNom = isset($_GET['filter_nom']) ? $_GET['filter_nom'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filterLocation = isset($_GET['filter_location']) ? $_GET['filter_location'] : '';
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'nom';
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

// Construire la requête avec filtres et tri
$query = "SELECT medicaments.*, stock_locations.location_name, stock_locations.bag_name 
          FROM medicaments 
          LEFT JOIN stock_locations ON medicaments.stock_location_id = stock_locations.id 
          WHERE 1=1";

if (!empty($filterNom)) {
    $query .= " AND nom LIKE :filterNom";
}
if (!empty($filterType)) {
    $query .= " AND type_produit = :filterType";
}
if (!empty($filterLocation)) {
    $query .= " AND stock_location_id = :filterLocation";
}

$query .= " ORDER BY $sortColumn $sortOrder";

$stmt = $conn->prepare($query);

if (!empty($filterNom)) {
    $stmt->bindValue(':filterNom', "%$filterNom%");
}
if (!empty($filterType)) {
    $stmt->bindValue(':filterType', $filterType);
}
if (!empty($filterLocation)) {
    $stmt->bindValue(':filterLocation', $filterLocation);
}

$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM stock_locations");
$stmt->execute();
$stockLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médicaments</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        .container {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2em;
            margin-bottom: 30px;
            color: #007bff;
            text-align: center;
        }

        .action-menu {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-action {
            font-size: 0.9em;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-back {
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #5a6268;
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-add {
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
        }

        .btn-add:hover {
            background-color: #218838;
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            padding: 15px;
            font-size: 1.2em;
        }

        .table {
            margin-top: 15px;
            font-size: 1em;
            border-spacing: 0 10px;
        }

        .table th, .table td {
            vertical-align: middle;
            padding: 15px;
            text-align: center;
        }

        .table tbody tr {
            background-color: #f9f9f9;
            border-radius: 10px;
        }

        .btn-warning, .btn-danger {
            border-radius: 15px;
            padding: 5px 15px;
            margin-right: 5px;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #000;
            border: none;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-warning:hover {
            background-color: #ffca2c;
        }

        .btn-danger:hover {
            background-color: #e02a32;
        }
        .table td .btn {
            display: block;
            width: 100%;
            margin-bottom: 8px;
        }

        .table td .btn:last-child {
            margin-bottom: 0;
        }

        .signature-pad {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestion des Médicaments</h1>

    <!-- Menu d'Actions -->
    <div class="action-menu">
        <a href="dashboard_medicaments.php" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a href="ajouter_medicament.php" class="btn-action btn-add">
            <i class="fas fa-plus-circle"></i> Ajouter un Médicament
        </a>
        <button class="btn btn-info" data-toggle="modal" data-target="#pdfModal">
            <i class="fas fa-file-pdf"></i> Générer Inventaire PDF
        </button>
    </div>

    <!-- Liste des Médicaments -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-capsules"></i> Médicaments Disponibles
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>N° de lot</th>
                        <th>Quantité</th>
                        <th>Date d'expiration</th>
                        <th>Type</th>
                        <th>Lieu de Stockage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicaments as $medicament): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($medicament['nom']); ?></td>
                            <td><?php echo htmlspecialchars($medicament['description']); ?></td>
                            <td><?php echo htmlspecialchars($medicament['numero_lot']); ?></td>
                            <td><?php echo htmlspecialchars($medicament['quantite']); ?></td>
                            <td><?php echo htmlspecialchars($medicament['date_expiration']); ?></td>
                            <td><?php echo htmlspecialchars($medicament['type_produit']); ?></td>
                            <td><?php echo htmlspecialchars($medicament['location_name'] . ($medicament['bag_name'] ? " - " . $medicament['bag_name'] : '')); ?></td>
                            <td>
                                <a href="medicament_details.php?id=<?php echo $medicament['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Voir Détails</a>
                                <a href="modifier_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Modifier</a>
                                <a href="supprimer_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?');"><i class="fas fa-trash-alt"></i> Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour sélectionner le lieu de stockage et capturer la signature pour le PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">Générer PDF d'Inventaire avec Signature</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="generer_pdf.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="locationSelect">Sélectionner le lieu de stockage</label>
                        <select class="form-control" id="locationSelect" name="location_id" required>
                            <?php foreach ($stockLocations as $location): ?>
                                <option value="<?php echo $location['id']; ?>">
                                    <?php echo htmlspecialchars($location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Signature :</label>
                        <div class="signature-pad">
                            <canvas id="signatureCanvas" width="400" height="150"></canvas>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" id="clearSignature">Effacer la signature</button>
                        <input type="hidden" name="signature" id="signatureInput">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Générer PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const canvas = document.getElementById('signatureCanvas');
    const signaturePad = new SignaturePad(canvas);

    document.getElementById('clearSignature').addEventListener('click', () => signaturePad.clear());

    document.querySelector('form').addEventListener('submit', function(e) {
        if (!signaturePad.isEmpty()) {
            document.getElementById('signatureInput').value = signaturePad.toDataURL('image/png');
        } else {
            alert("Veuillez ajouter une signature.");
            e.preventDefault();
        }
    });
</script>
</body>
</html>
