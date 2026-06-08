<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = \App\Models\User::find(1);
    if (!$user) {
        echo "User not found\n";
        exit(1);
    }
    
    $variantId = env('LS_VARIANT_FREE_MONTHLY');
    echo "Variant ID: $variantId\n";
    echo "Store: " . env('LEMON_SQUEEZY_STORE') . "\n";
    echo "API Key: " . substr(env('LEMON_SQUEEZY_API_KEY'), 0, 20) . "...\n";
    
    $checkout = $user->checkout($variantId, ['name' => 'Test'], ['plan_id' => '1'])
        ->redirectTo(route('dashboard', false));
    
    echo "Checkout object created\n";
    echo "Getting URL...\n";
    $url = $checkout->url();
    echo "Checkout URL: $url\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
