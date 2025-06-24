<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validation des données
        $requiredFields = ['utilisateur_id', 'intitule'];
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

        // Vérification de la longueur de l'intitulé
        if (strlen($data['intitule']) > 100) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "L'intitulé ne doit pas dépasser 100 caractères"
            ]);
            exit;
        }

        // Insertion
        $stmt = $conn->prepare("
            INSERT INTO certification (
                utilisateur_id, 
                intitule, 
                date_obtention
            ) VALUES (?, ?, ?)
        ");
        
        // Gestion optionnelle de la date
        $dateObtention = !empty($data['date_obtention']) ? $data['date_obtention'] : NULL;
        $stmt->bind_param("iss", 
            $data['utilisateur_id'],
            $data['intitule'],
            $dateObtention
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Certification ajoutée avec succès',
                'idcertification' => $conn->insert_id
            ]);
        } else {
            throw new Exception('Erreur lors de l\'ajout de la certification');
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