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
        $requiredFields = ['idmission', 'titre', 'date_debut', 'date_fin'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Le champ $field est requis"
                ]);
                exit;
            }
        }

        // Vérification des dates
        if (strtotime($data['date_debut']) > strtotime($data['date_fin'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La date de fin doit être postérieure à la date de début'
            ]);
            exit;
        }

        // Mise à jour
        $stmt = $conn->prepare("
            UPDATE mission 
            SET 
                titre = ?,
                description = ?,
                date_debut = ?,
                date_fin = ?,
                offre_id = ?
            WHERE idmission = ?
        ");
        
        $stmt->bind_param("ssssii", 
            $data['titre'],
            $data['description'],
            $data['date_debut'],
            $data['date_fin'],
            $data['idoffre'],
            $data['idmission']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Mission mise à jour avec succès'
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