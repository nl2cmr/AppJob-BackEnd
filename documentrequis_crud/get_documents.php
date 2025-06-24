<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                d.iddocumentreq,
                d.intitule,
                o.reference as reference_offre,
                o.idoffre
            FROM 
                document_requis d
            JOIN 
                offre o ON d.offre_id = o.idoffre
            ORDER BY 
                o.reference ASC, d.intitule ASC
        ");
        $stmt->execute();
        
        $result = $stmt->get_result();
        $documents = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($documents);
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