<?php
session_start();
$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations utilisateur
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les documents actuels
    $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Mon profil</title>
    <style>
        .profile-container {
            background-color: #f7f9fc;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .profile-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .form-control, .btn {
            border-radius: 50px;
            padding: 12px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .document-upload {
            padding: 20px;
            background-color: #e9f1ff;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="profile-container">
        <div class="profile-header">
            <h1>Documents de l'utilisateur : <?php echo htmlspecialchars($user['username']); ?></h1>
        </div>

        <!-- Formulaire pour télécharger des documents -->
        <div class="document-upload">
            <form method="POST" enctype="multipart/form-data" action="upload_document.php">
                <div class="form-group">
                    <label for="documents">Télécharger des documents</label>
                    <input type="file" class="form-control" id="documents" name="documents[]" multiple>
                </div>
                <div id="document-names"></div> <!-- Champ pour renommer les documents -->
                <button type="submit" class="btn btn-success btn-block">Télécharger</button>
            </form>
        </div>

        <!-- Liste des documents -->
        <h4>Documents Actuels</h4>
        <?php if (!empty($documents)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nom du Document</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn btn-info btn-sm" target="_blank">Voir</a>
                                <a href="delete_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun document disponible pour cet utilisateur.</p>
        <?php endif; ?>

        <!-- Bouton pour retourner à la gestion des utilisateurs -->
        <a href="manage_users.php" class="btn btn-secondary mt-4">Retour à la gestion des utilisateurs</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Ajouter des champs de texte pour renommer les documents
    document.getElementById('documents').addEventListener('change', function () {
        const fileList = this.files;
        const container = document.getElementById('document-names');
        container.innerHTML = '';  // Vider les anciens champs

        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control mt-2';
            input.name = 'document_names[]';  // Nom du champ pour récupérer les noms
            input.placeholder = `Nom pour ${file.name}`;
            container.appendChild(input);
        }
    });
</script>

</body>
</html>
