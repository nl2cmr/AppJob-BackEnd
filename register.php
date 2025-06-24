<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$data = json_decode(file_get_contents('php://input'), true);

$response = [
    'success' => false, 
    'message' => '', 
    'errors' => [],
    'fieldErrors' => []
];

$allowedRoles = ['candidate', 'recruiter'];
if (!isset($data['role']) || !in_array($data['role'], $allowedRoles)) {
    $response['message'] = 'Rôle invalide';
    echo json_encode($response);
    exit;
}

$requiredFields = [
    'email' => 'Email est requis',
    'password' => 'Mot de passe est requis',
    'confirmpassword' => 'Confirmation du mot de passe est requise',
    'nom' => 'Nom est requis',
    'telephone' => 'Téléphone est requis',
    'adresse' => 'Adresse est requise'
];

if ($data['role'] === 'candidate') {
    $requiredFields['prenom'] = 'Prénom est requis';
    $requiredFields['date_naiss'] = 'Date de naissance est requise';
}

foreach ($requiredFields as $field => $errorMessage) {
    if (empty($data[$field])) {
        $response['fieldErrors'][$field] = $errorMessage;
    }
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) && !isset($response['fieldErrors']['email'])) {
    $response['fieldErrors']['email'] = 'Email non valide';
}

if (strlen($data['password']) < 8 && !isset($response['fieldErrors']['password'])) {
    $response['fieldErrors']['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
}

if ($data['password'] !== $data['confirmpassword'] && !isset($response['fieldErrors']['confirmpassword'])) {
    $response['fieldErrors']['confirmpassword'] = 'Les mots de passe ne correspondent pas';
}

if (!empty($response['fieldErrors'])) {
    $response['message'] = 'Veuillez corriger les erreurs dans le formulaire';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT iduser FROM utilisateur WHERE email = ?");
    $stmt->execute([$data['email']]);
    
    if ($stmt->fetch()) {
        $response['fieldErrors']['email'] = 'Cet email est déjà utilisé';
        $response['message'] = 'Cet email est déjà associé à un compte';
        echo json_encode($response);
        exit;
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    $dbRole = $data['role'] === 'candidate' ? 'candidat' : 'recruteur';

    $userData = [
        'email' => sanitizeInput($data['email']),
        'mot_de_passe' => $hashedPassword,
        'role' => $dbRole,
        'nom' => sanitizeInput($data['nom']),
        'telephone' => sanitizeInput($data['telephone']),
        'adresse' => sanitizeInput($data['adresse'])
    ];

    if ($dbRole === 'candidat') {
        $userData['prenom'] = sanitizeInput($data['prenom']);
        $userData['date_naissance'] = $data['date_naiss'];
    }

    $columns = implode(', ', array_keys($userData));
    $placeholders = ':' . implode(', :', array_keys($userData));
    
    $sql = "INSERT INTO utilisateur ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($userData);
    
    $response['success'] = true;
    $response['message'] = 'Inscription réussie! Vous pouvez maintenant vous connecter.';

    if (isset($data['rememberMe']) && $data['rememberMe']) {
        // Vous pourriez ici générer un token de session persistante
        // Mais c'est généralement géré lors de la connexion
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $response['message'] = 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.';
}

echo json_encode($response);
?>