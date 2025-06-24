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
        if (empty($data['iddocumentreq'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID document requis manquant'
            ]);
            exit;
        }

        // Suppression
        $stmt = $conn->prepare("DELETE FROM document_requis WHERE iddocumentreq = ?");
        $stmt->bind_param("i", $data['iddocumentreq']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Document requis supprimé avec succès'
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