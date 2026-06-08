<?php

// Test the chatbot
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Check if ChatbotController exists
if (class_exists('App\Http\Controllers\ChatbotController')) {
    echo "✓ ChatbotController found!\n";
} else {
    echo "✗ ChatbotController NOT found!\n";
    exit(1);
}

// Check if ChatbotFlowService exists
if (class_exists('App\Services\ChatbotFlowService')) {
    echo "✓ ChatbotFlowService found!\n";
    
    // Load flows
    $flows = \App\Services\ChatbotFlowService::getFlows('ar');
    echo "✓ Arabic flows loaded: " . count($flows['root']['flows']) . " main flows\n";
    
    $flows_en = \App\Services\ChatbotFlowService::getFlows('en');
    echo "✓ English flows loaded: " . count($flows_en['root']['flows']) . " main flows\n";
} else {
    echo "✗ ChatbotFlowService NOT found!\n";
    exit(1);
}

echo "\n✓ All components loaded successfully!\n";
