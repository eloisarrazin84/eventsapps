<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <title>Mes Applications</title>
    <style>
       .app-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    justify-items: center;
    margin-top: 50px;
}

.app-icon {
    width: 180px;
    height: 180px;
    background: linear-gradient(135deg, #007bff, #00d4ff);
    border-radius: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
}

.app-icon:hover {
    transform: scale(1.1);
    box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.3);
    cursor: pointer;
}

.app-icon img {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
}

.app-name {
    font-size: 1.2rem;
    color: white;
}
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mt-5">Mes Applications</h1>
    <div class="app-container">

        <!-- Application 1: Mes événements -->
        <div class="app-icon" onclick="window.location.href='dashboard.php'">
            <i class="bi bi-calendar-event"></i>
            <div class="app-name">Mes événements</div>
        </div>

        <!-- Application 2: Future Application -->
        <div class="app-icon" onclick="window.location.href='#'">
            <i class="bi bi-app"></i>
            <div class="app-name">Future App</div>
        </div>

        <!-- Ajouter d'autres applications ici -->

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
