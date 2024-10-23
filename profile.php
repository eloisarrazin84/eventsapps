<?php
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo "Erreur : utilisateur non connecté.";
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gestion de l'upload de documents
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $uploadDir = 'uploads/';

        // Vérifier si le répertoire existe, sinon le créer
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filePath = $uploadDir . basename($file['name']);
        $documentName = htmlspecialchars(basename($file['name']));

        // Vérification du succès de l'upload
        if ($file['error'] === UPLOAD_ERR_OK && move_uploaded_file($file['tmp_name'], $filePath)) {
            // Enregistrer le document dans la base de données
            $stmt = $conn->prepare("INSERT INTO documents (user_id, document_name, file_path) VALUES (:user_id, :document_name, :file_path)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':document_name', $documentName);
            $stmt->bindParam(':file_path', $filePath);
            $stmt->execute();
            echo "Fichier téléchargé avec succès.";
        } else {
            echo "Erreur lors du téléchargement du fichier.";
        }
    }

    // Récupérer les documents actuels de l'utilisateur
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
        .card {
            margin-top: 20px;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .document-upload {
            padding: 20px;
            background-color: #e9f1ff;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="profile-container">
        <div class="profile-header">
            <h1>Votre profil</h1>
        </div>

        <!-- Formulaire pour télécharger des documents -->
        <div class="document-upload">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Télécharger un document</label>
                    <input type="file" class="form-control" id="file" name="file" required>
                </div>
                <button type="submit" class="btn btn-success btn-block">Télécharger</button>
            </form>
        </div>

        <!-- Liste des documents -->
        <h4>Documents Actuels</h4>
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
                            <a href="view_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-info btn-sm">Voir</a>
                            <a href="delete_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bouton de retour au tableau de bord en fin de page -->
    <div class="mt-5 text-center">
        <a href="dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
