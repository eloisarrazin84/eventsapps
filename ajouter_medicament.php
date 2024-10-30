<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Ajouter un Médicament</title>
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
            margin-top: 10px;
        }
        .progress-bar {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            height: 8px;
            margin-right: 10px;
        }
        .progress-bar-inner {
            height: 100%;
            background-color: #007bff;
            transition: width 0.3s;
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
                <label class="icon-label" for="medicament_nom"><i class="fas fa-pills"></i> Nom du médicament</label>
                <input type="text" class="form-control" id="medicament_nom" name="medicament_nom" required>
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
        
        <!-- Barre de progression -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-bar-inner" id="progress"></div>
            </div>
            <span id="progressText">0%</span>
        </div>

        <!-- Bouton Ajouter -->
        <button type="submit" class="btn btn-success btn-block mt-4">Ajouter</button>
    </form>
</div>

<!-- Scripts JavaScript -->
<script>
    const form = document.getElementById('medicamentForm');
    const progress = document.getElementById('progress');
    const progressText = document.getElementById('progressText');

    form.addEventListener('input', () => {
        const filledFields = [...form.elements].filter(el => el.value !== "" && el.type !== "submit").length;
        const totalFields = form.elements.length - 1; // Exclude the submit button
        const percentage = Math.round((filledFields / totalFields) * 100);
        
        progress.style.width = percentage + "%";
        progressText.innerText = percentage + "%";
    });
</script>
</body>
</html>
