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
                d.iddiplome,
                d.titre,
                DATE_FORMAT(d.date_obtention, '%Y-%m-%d') as date_obtention,
                o.reference as reference_offre,
                o.idoffre
            FROM 
                diplome d
            LEFT JOIN 
                offre o ON d.offre_id = o.idoffre
            WHERE 
                d.utilisateur_id = ?
            ORDER BY 
                d.date_obtention DESC
        ");
        $stmt->bind_param("i", $iduser);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $diplomes = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($diplomes);
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