<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required files like the existing login.php does
require_once __DIR__ . '/../../src/Helpers/env.php';
require_once __DIR__ . '/../../src/Helpers/database.php';
require_once __DIR__ . '/../../src/Helpers/session.php';
require_once __DIR__ . '/../../src/Helpers/auth.php';
require_once __DIR__ . '/../../src/Services/AuthService.php';

loadEnv(__DIR__ . '/../../.env');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit;
    }
    
    $authService = new AuthService();
    $result = $authService->authenticate($input['username'], $input['password']);
    
    if ($result['success']) {
        // Get user data
        $user = $result['user'];
        
        // Create a simple token
        $token = base64_encode($user['user_id'] . ':' . time());
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'grade_level' => $user['grade_level'] ?? null,
                'created_at' => $user['created_at']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Invalid credentials'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
