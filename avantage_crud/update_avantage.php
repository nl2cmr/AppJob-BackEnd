<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validation
        if (empty($data['idavantage'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "ID avantage manquant"
            ]);
            exit;
        }

        if (empty($data['description'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "La description est requise"
            ]);
            exit;
        }

        // Vérification de la longueur
        if (strlen($data['description']) > 500) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "La description ne doit pas dépasser 500 caractères"
            ]);
            exit;
        }

        // Mise à jour
        $stmt = $conn->prepare("
            UPDATE avantage 
            SET 
                description = ?,
                offre_id = ?
            WHERE idavantage = ?
        ");
        
        $stmt->bind_param("sii", 
            $data['description'],
            $data['idoffre'],
            $data['idavantage']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Avantage mis à jour avec succès'
            ]);
        } else {
            throw new Exception('Erreur lors de la mise à jour');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
    }
}

$conn->close();
?>