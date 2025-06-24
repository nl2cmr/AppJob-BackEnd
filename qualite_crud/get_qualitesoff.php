<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $iduser = $_GET['iduser'] ?? 0;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                q.idqualite,
                q.intitule,
                q.description,
                o.reference as reference_offre
            FROM 
                qualite q
            LEFT JOIN 
                offre o ON q.offre_id = o.idoffre
            WHERE 
                q.utilisateur_id = ?
            ORDER BY 
                q.intitule ASC
        ");
        $stmt->bind_param("i", $iduser);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $qualites = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($qualites);
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