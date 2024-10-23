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
    animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
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
    transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
    position: relative;
}

.app-icon:hover {
    transform: scale(1.1) translateY(-5px);
    box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    background: linear-gradient(135deg, #00d4ff, #007bff);
}

.app-icon::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    border-radius: 15px;
    opacity: 0;
    background: rgba(255, 255, 255, 0.3);
    transition: opacity 0.3s ease;
}

.app-icon:hover::after {
    opacity: 1;
}

.app-name {
    font-size: 1.2rem;
    color: white;
}

.app-icon img {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
}

.app-icon svg {
    width: 60px;
    height: 60px;
    color: white;
    margin-bottom: 10px;
}

    </style>
</head>
<body>
<div class="app-container">
    <!-- Application 1: Dashboard -->
    <div class="app-icon" onclick="window.location.href='dashboard.php'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-speedometer2" viewBox="0 0 16 16">
            <path d="M8 4a.5.5 0 0 0-1 0v3.245l-1.29 2.56a.5.5 0 1 0 .87.49L7.615 8H9a.5.5 0 0 0 0-1H8V4z"/>
            <path fill-rule="evenodd" d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1z"/>
        </svg>
        <div class="app-name">Mes événements</div>
    </div>

    <!-- Application 2: Future App -->
    <div class="app-icon" onclick="window.location.href='#'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
            <path d="M8.165 15.803c-2.06-.085-4.207-1.2-5.684-3.396C1.203 11.32.986 9.673.986 8c0-1.673.217-3.32 1.495-4.407C4.208 1.397 6.355.282 8.415.197c2.06.085 4.207 1.2 5.684 3.396C14.797 4.68 15.014 6.327 15.014 8c0 1.673-.217 3.32-1.495 4.407C12.792 14.603 10.645 15.718 8.585 15.803zm0-1.158c1.723-.084 3.52-.944 4.777-2.735C14.078 11.097 14.183 9.65 14.183 8c0-1.65-.105-3.097-1.241-4.14C11.68 2.441 9.883 1.58 8.16 1.496 6.437 1.58 4.64 2.441 3.383 3.725c-1.136 1.043-1.241 2.49-1.241 4.14 0 1.65.105 3.097 1.241 4.14 1.257 1.781 3.054 2.65 4.777 2.735z"/>
        </svg>
        <div class="app-name">Future App</div>
    </div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
