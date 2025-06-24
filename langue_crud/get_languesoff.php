<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "apprecherche");

// Vérifier la connexion
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Échec de la connexion à la base de données: ' . $conn->connect_error
    ]));
}

// Récupérer l'ID utilisateur
$iduser = $_GET['iduser'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Préparer la requête avec jointure pour récupérer la référence de l'offre
        $stmt = $conn->prepare("
            SELECT 
                l.idlangue, 
                l.intitule, 
                l.niveau,
                o.reference as reference_offre
            FROM 
                langue l
            LEFT JOIN 
                offre o ON l.offre_id = o.idoffre
            WHERE 
                l.utilisateur_id = ?
        ");
        
        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête: " . $conn->error);
        }

        $stmt->bind_param("i", $iduser);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $langues = $result->fetch_all(MYSQLI_ASSOC);

        // Vérifier si des langues ont été trouvées
        if (empty($langues)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Aucune langue trouvée pour cet utilisateur'
            ]);
        } else {
            echo json_encode($langues);
        }

        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>