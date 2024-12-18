<?php
// Lire le fichier CIS_bdpm.txt et extraire les noms des médicaments
$medicamentNames = [];
$file = fopen("CIS_bdpm.txt", "r");

while (($line = fgets($file)) !== false) {
    $fields = explode("\t", $line); // Supposons que les champs soient séparés par des tabulations
    if (isset($fields[1])) { // Le nom du médicament est dans le deuxième champ
        $medicamentNames[] = trim($fields[1]);
    }
}
fclose($file);

// Connexion à la base de données pour récupérer les lieux de stockage
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT * FROM stock_locations");
$stmt->execute();
$stockLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>Ajouter un Médicament</title>
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .icon-label {
            display: flex;
            align-items: center;
            font-weight: bold;
        }
        .icon-label i {
            margin-right: 8px;
            color: #007bff;
        }
        .btn-submit, .btn-cancel {
            font-size: 1.2em;
            padding: 12px 24px;
            transition: all 0.3s ease;
            border-radius: 30px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
            margin-left: 15px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Ajouter un Médicament</h2>
    
    <form method="POST" action="process_ajout_medicament.php" enctype="multipart/form-data" id="medicamentForm">
        <!-- Section Informations Générales -->
        <div class="form-section">
            <h4>Informations Générales</h4>
            <div class="form-group">
                <label class="icon-label" for="medicament_nom">
                    <i class="fas fa-pills"></i> Nom du médicament
                    <span class="tooltip-icon" data-toggle="tooltip" title="Nom complet du médicament."><i class="fas fa-info-circle"></i></span>
                </label>
                <input type="text" class="form-control" id="medicament_nom" name="medicament_nom" required autocomplete="off">
            </div>
            <div class="form-group">
                <label class="icon-label" for="numero_lot"><i class="fas fa-barcode"></i> Numéro de lot</label>
                <input type="text" class="form-control" id="numero_lot" name="numero_lot" required>
            </div>
            <div class="form-group">
                <label class="icon-label" for="description"><i class="fas fa-align-left"></i> Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
        </div>
        
        <!-- Section Détails Supplémentaires -->
        <div class="form-section">
            <h4>Détails Supplémentaires</h4>
            <div class="form-group">
                <label class="icon-label" for="quantite"><i class="fas fa-sort-numeric-up-alt"></i> Quantité</label>
                <input type="number" class="form-control" id="quantite" name="quantite" required>
            </div>
            <div class="form-group">
                <label class="icon-label" for="date_expiration"><i class="fas fa-calendar-alt"></i> Date d'expiration</label>
                <input type="date" class="form-control" id="date_expiration" name="date_expiration" required>
            </div>
            <div class="form-group">
                <label class="icon-label" for="type_produit"><i class="fas fa-vial"></i> Type de Produit</label>
                <select class="form-control" id="type_produit" name="type_produit" required>
                    <option value="PER OS">PER OS</option>
                    <option value="Injectable">Injectable</option>
                    <option value="Inhalable">Inhalable</option>
                </select>
            </div>
            <!-- Section pour ajouter la photo du médicament -->
<div class="form-section">
    <h4>Photo du Médicament</h4>
    <div class="form-group">
        <label class="icon-label" for="photo"><i class="fas fa-image"></i> Téléchargez une photo</label>
        <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
    </div>
</div>

        </div>

        <!-- Section Lieu de Stockage -->
        <div class="form-section">
            <h4>Lieu de Stockage</h4>
            <div class="form-group">
                <label class="icon-label" for="stock_location_id"><i class="fas fa-warehouse"></i> Lieu de stockage</label>
                <select class="form-control" id="stock_location_id" name="stock_location_id" required>
                    <?php foreach ($stockLocations as $location): ?>
                        <option value="<?php echo $location['id']; ?>">
                            <?php echo htmlspecialchars($location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Boutons Ajouter et Annuler -->
        <div class="button-group">
            <button type="submit" class="btn btn-submit">Ajouter</button>
            <a href="dashboard_medicaments.php" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>

<!-- Scripts JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    // Initialisation des tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Passer la liste des noms de médicaments à JavaScript pour l'autocomplétion
    const medicamentNames = <?php echo json_encode($medicamentNames); ?>;
    
    // Activer l'autocomplétion pour le champ "Nom du médicament"
    $(document).ready(function () {
        $("#medicament_nom").autocomplete({
            source: medicamentNames
        });
    });
</script>
</body>
</html>
