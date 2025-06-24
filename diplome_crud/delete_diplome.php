<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validation
        if (empty($data['iddiplome'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID diplôme manquant'
            ]);
            exit;
        }

        // Vérification des droits
        $check = $conn->prepare("SELECT 1 FROM diplome WHERE iddiplome = ? AND utilisateur_id = ?");
        $check->bind_param("ii", $data['iddiplome'], $data['iduser']);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Non autorisé'
            ]);
            exit;
        }

        // Suppression
        $stmt = $conn->prepare("DELETE FROM diplome WHERE iddiplome = ?");
        $stmt->bind_param("i", $data['iddiplome']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Diplôme supprimé avec succès'
            ]);
        } else {
            throw new Exception('Erreur lors de la suppression');
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