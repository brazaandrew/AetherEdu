<?php
// Simple API entry point to verify Vercel PHP runtime is active
header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'Vercel PHP API is successfully connected and running!',
    'environment' => 'Vercel Serverless Function',
    'php_version' => phpversion(),
    'time' => date('Y-m-d H:i:s')
]);
