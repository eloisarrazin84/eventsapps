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
    <title>Mes Applications</title>
    <style>
        .app-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 50px;
        }

        .app-icon {
            width: 150px;
            height: 150px;
            border: 2px solid #007bff;
            border-radius: 10px;
            margin: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .app-icon:hover {
            transform: scale(1.1);
            cursor: pointer;
        }

        .app-icon img {
            width: 70px;
            height: 70px;
        }

        .app-name {
            margin-top: 10px;
            font-size: 1.2rem;
            color: #007bff;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mt-5">Mes Applications</h1>
    <div class="app-container">

        <!-- Application 1: Dashboard -->
        <div class="app-icon" onclick="window.location.href='dashboard.php'">
            <img src="icons/dashboard-icon.png" alt="Dashboard">
            <div class="app-name">Dashboard</div>
        </div>

        <!-- Application 2: Future Application -->
        <div class="app-icon" onclick="window.location.href='#'">
            <img src="icons/future-app-icon.png" alt="Future App">
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
