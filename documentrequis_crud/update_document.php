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
        if (empty($data['iddocumentreq'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "ID document requis manquant"
            ]);
            exit;
        }

        if (empty($data['intitule'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "L'intitulé est requis"
            ]);
            exit;
        }

        // Vérification de la longueur
        if (strlen($data['intitule']) > 100) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "L'intitulé ne doit pas dépasser 100 caractères"
            ]);
            exit;
        }

        // Mise à jour
        $stmt = $conn->prepare("
            UPDATE document_requis 
            SET 
                intitule = ?,
                offre_id = ?
            WHERE iddocumentreq = ?
        ");
        
        $stmt->bind_param("sii", 
            $data['intitule'],
            $data['idoffre'],
            $data['iddocumentreq']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Document requis mis à jour avec succès'
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