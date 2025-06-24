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
        if (empty($data['idmission'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID mission manquant'
            ]);
            exit;
        }

        // Suppression
        $stmt = $conn->prepare("DELETE FROM mission WHERE idmission = ?");
        $stmt->bind_param("i", $data['idmission']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Mission supprimée avec succès'
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
    }
}

$conn->close();
?>