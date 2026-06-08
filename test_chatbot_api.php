<?php

/**
 * Test the chatbot endpoint
 */

// Simulate a POST request to /api/chatbot/process
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Create app
$app->make(\Illuminate\Contracts\Http\Kernel::class);

// Simulate request
$request = \Illuminate\Http\Request::create('/api/chatbot/process', 'POST', [], [], [], [
    'HTTP_CONTENT_TYPE' => 'application/json',
    'HTTP_X_CSRF_TOKEN' => 'test-token'
], json_encode([
    'session_id' => 'test_session_' . time(),
    'action' => 'flow',
    'language' => 'ar'
]));

// Bind request to app
$app->instance('request', $request);

// Get controller
$controller = new \App\Http\Controllers\ChatbotController();

// Test 1: flow action
echo "Test 1: Getting root menu (flow action)...\n";
try {
    $response = $controller->handleChat($request);
    $data = json_decode($response->getContent(), true);
    echo "✓ Response received\n";
    echo "  - Message length: " . strlen($data['reply'] ?? '') . " chars\n";
    echo "  - Flow type: " . ($data['flow'] ?? 'unknown') . "\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: chat action with message
echo "\nTest 2: Chat with text message (should detect 'pricing' flow)...\n";
$request2 = \Illuminate\Http\Request::create('/api/chatbot/process', 'POST', [], [], [], [
    'HTTP_CONTENT_TYPE' => 'application/json',
], json_encode([
    'session_id' => 'test_session_' . time(),
    'action' => 'chat',
    'language' => 'ar',
    'message' => 'كم السعر؟'
]));

$app->instance('request', $request2);
$controller2 = new \App\Http\Controllers\ChatbotController();

try {
    $response2 = $controller2->handleChat($request2);
    $data2 = json_decode($response2->getContent(), true);
    echo "✓ Response received\n";
    echo "  - Message: " . ($data2['reply'] ?? 'none') . "\n";
    echo "  - Flow type: " . ($data2['flow'] ?? 'unknown') . "\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n✓ All tests completed!\n";
