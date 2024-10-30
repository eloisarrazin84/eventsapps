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
        .progress-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        .progress-bar {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            height: 10px;
            margin-right: 10px;
        }
        .progress-bar-inner {
            height: 100%;
            background-color: #007bff;
            transition: width 0.3s;
        }
        .progress-bar-inner.low { background-color: #dc3545; }
        .progress-bar-inner.medium { background-color: #ffc107; }
        .progress-bar-inner.high { background-color: #28a745; }
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
    
    <form method="POST" action="process_ajout_medicament.php" id="medicamentForm">
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

    // Gestion de la barre de progression
    const form = document.getElementById('medicamentForm');
    const progress = document.getElementById('progress');
    const progressText = document.getElementById('progressText');

    form.addEventListener('input', () => {
        const filledFields = [...form.elements].filter(el => el.value !== "" && el.type !== "submit").length;
        const totalFields = form.elements.length - 1; // Exclude the submit button
        const percentage = Math.round((filledFields / totalFields) * 100);
        
        progress.style.width = percentage + "%";
        progressText.innerText = percentage + "%";
        
        if (percentage <= 33) {
            progress.className = 'progress-bar-inner low';
        } else if (percentage <= 66) {
            progress.className = 'progress-bar-inner medium';
        } else {
            progress.className = 'progress-bar-inner high';
        }
    });

    <?php
    // Charger les noms des médicaments depuis le fichier `CIS_bdpm.txt`
    $medicamentNames = [];
    if (file_exists('CIS_bdpm.txt')) {
        $file = fopen('CIS_bdpm.txt', 'r');
        while (($line = fgets($file)) !== false) {
            $medicamentNames[] = trim($line); // Enlever les espaces autour
        }
        fclose($file);
    }
    ?>

    // Passer la liste des noms de médicaments à JavaScript
    const medicamentNames = <?php echo json_encode($medicamentNames); ?>;

    // Autocomplétion pour le champ "Nom du médicament"
    $(document).ready(function () {
        $("#medicament_nom").autocomplete({
            source: medicamentNames
        });
    });
</script>
</body>
</html>
