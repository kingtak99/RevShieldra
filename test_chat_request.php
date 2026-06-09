<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$payload = ['session_id' => 'test_session_'.time(), 'action' => 'chat', 'language' => 'ar', 'message' => 'كم السعر؟'];
$server = ['HTTP_HOST' => 'localhost', 'SERVER_NAME' => 'localhost', 'SERVER_PORT' => '80', 'REMOTE_ADDR' => '127.0.0.1', 'HTTP_CONTENT_TYPE' => 'application/json'];
$request = Illuminate\Http\Request::create('/api/chatbot/process', 'POST', [] ,[] ,[] , $server, json_encode($payload));
$response = $kernel->handle($request);
echo $response->getStatusCode()."\n";
var_export($response->headers->all());
