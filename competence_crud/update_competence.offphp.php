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
        if (empty($data['intutile'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'L\'intitulé est requis']);
            exit;
        }

        // Vérification des droits
        $check = $conn->prepare("
            SELECT 1 FROM competence c
            LEFT JOIN offre o ON c.offre_id = o.idoffre
            WHERE c.idcompetence = ? AND (c.utilisateur_id = ? OR o.recruteur_id = ?)
        ");
        $check->bind_param("iii", $data['idcompetence'], $data['iduser'], $data['iduser']);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }

        // Mise à jour
        $stmt = $conn->prepare("
            UPDATE competence 
            SET intutile = ?, niveau = ?
            WHERE idcompetence = ?
        ");
        $stmt->bind_param("ssi", 
            $data['intutile'],
            $data['niveau'],
            $data['idcompetence']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Erreur lors de la mise à jour');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check)) $check->close();
    }
}

$conn->close();
?>