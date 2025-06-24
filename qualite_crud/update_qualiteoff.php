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
        $requiredFields = ['idqualite', 'iduser', 'intitule', 'description'];
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

        // Vérification des droits
        $check = $conn->prepare("
            SELECT 1 FROM qualite q
            LEFT JOIN offre o ON q.offre_id = o.idoffre
            WHERE q.idqualite = ? AND (q.utilisateur_id = ? OR o.recruteur_id = ?)
        ");
        $check->bind_param("iii", $data['idqualite'], $data['iduser'], $data['iduser']);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Non autorisé'
            ]);
            exit;
        }

        // Mise à jour
        $stmt = $conn->prepare("
            UPDATE qualite 
            SET 
                intitule = ?,
                description = ?,
                offre_id = ?
            WHERE idqualite = ?
        ");
        
        $offreId = !empty($data['idoffre']) ? $data['idoffre'] : NULL;
        $stmt->bind_param("ssii", 
            $data['intitule'],
            $data['description'],
            $offreId,
            $data['idqualite']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Qualité mise à jour avec succès'
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
        if (isset($check)) $check->close();
    }
}

$conn->close();
?>