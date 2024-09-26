<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top 3 Films</title>
    <style>
        body { background: #161616; color: white; font-family: Arial; }
        .top3 {
            display: flex;
            align-items: center;
            flex-direction: column;
        }
        ol.top3__list {
            list-style: none;
            counter-reset: rank-counter;
            display: flex;
            padding: 0;
        }
        .top3__item {
            counter-increment: rank-counter;
            position: relative;
            padding-left: 130px;
            height: 200px;
            transition: transform 0.5s ease;
            transform: scale(1);
        }
        .top3__item::before {
            content: counter(rank-counter);
            color: #161616;
            font-weight: bold;
            line-height: 200px;
            position: absolute;
            height: 100%;
            width: 165px;
            left: 0;
            font-size: 260px;
            z-index: -1;
            overflow: hidden;
            -webkit-text-stroke-width: 6px;
            -webkit-text-stroke-color: #666;
            text-align: right;
        }
        .top3__list:hover .top3__item {
            transform: translateX(-50px);
        }
        .top3__list:hover .top3__item:hover {
            transform: translateX(0) scale(1.5);
        }
        .top3__item:hover ~ .top3__item {
            transform: translateX(50px);
        }
        .top3__image {
            height: 100%;
        }
    </style>
</head>
<body>
    
<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kata-1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sélectionner le titre du film, le nom de la catégorie et la note
    $stmt = $pdo->query("
        SELECT m.title, c.name AS category, m.rate
        FROM movie m
        JOIN category c ON m.category_id = c.id
        ORDER BY m.rate DESC
        LIMIT 3
    ");

    $topMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des affiches à partir de l'API OMDB
    $apiKey = "3fc9db42";
    $baseUrl = "http://www.omdbapi.com/?apikey={$apiKey}&t=";

    // Création d'un tableau pour stocker les films avec leurs affiches
    $moviesWithPosters = [];

    foreach ($topMovies as $movie) {
        $movieTitle = urlencode($movie['title']); // Échappe les espaces et caractères spéciaux
        $response = file_get_contents($baseUrl . $movieTitle);
        $data = json_decode($response, true);

        // Vérifie si l'affiche existe dans la réponse
        if (isset($data['Poster'])) {
            $moviesWithPosters[] = [
                'title' => $movie['title'],
                'category' => $movie['category'],
                'rate' => $movie['rate'],
                'poster' => $data['Poster']
            ];
        }
    }

    // Affichage des films
    echo '<div class="top3">';
    echo '<h2>Les trois meilleures notes parmi tous les films</h2>';
    echo '<ol class="top3__list">';
    
    foreach ($moviesWithPosters as $movie) {
        echo '<li class="top3__item">';
        echo '<img class="top3__image" src="' . $movie['poster'] . '" alt="' . $movie['title'] . ' poster" />';
        echo '<div><strong>' . htmlspecialchars($movie['title']) . '</strong><br>(' . htmlspecialchars($movie['category']) . ') <br><i>Note: ' . htmlspecialchars($movie['rate']) . '</i></div>';
        echo '</li>';
    }

    echo '</ol>';
    echo '</div>';

} catch (PDOException $e) {
    echo 'Erreur de connexion : ' . $e->getMessage();
}
?>
</body>
</html>
