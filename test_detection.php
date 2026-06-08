<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Test detectFlowFromMessage method
$reflection = new ReflectionMethod(\App\Http\Controllers\ChatbotController::class, 'detectFlowFromMessage');
$reflection->setAccessible(true);

$controller = new \App\Http\Controllers\ChatbotController();

echo "Testing detectFlowFromMessage method:\n";
echo "=====================================\n\n";

// Test 1: "كم السعر؟"
echo "Test 1: Message = 'كم السعر؟'\n";
$result = $reflection->invoke($controller, 'كم السعر؟', 'ar');
echo "  Result: " . json_encode($result) . "\n";

// Test 2: "سعر"
echo "\nTest 2: Message = 'سعر'\n";
$result = $reflection->invoke($controller, 'سعر', 'ar');
echo "  Result: " . json_encode($result) . "\n";

// Test 3: "pricing"
echo "\nTest 3: Message = 'pricing'\n";
$result = $reflection->invoke($controller, 'pricing', 'en');
echo "  Result: " . json_encode($result) . "\n";

// Test 4: "how much"
echo "\nTest 4: Message = 'how much'\n";
$result = $reflection->invoke($controller, 'how much', 'en');
echo "  Result: " . json_encode($result) . "\n";

// Test normalization
echo "\n\nTesting normalizeArabicText:\n";
echo "============================\n\n";

$normalizeReflection = new ReflectionMethod(\App\Http\Controllers\ChatbotController::class, 'normalizeArabicText');
$normalizeReflection->setAccessible(true);

$messages = ['كم السعر؟', 'سعر', 'الخطة', 'الاشتراك'];
foreach ($messages as $msg) {
    $normalized = $normalizeReflection->invoke($controller, $msg);
    echo "  '$msg' -> '$normalized'\n";
}
