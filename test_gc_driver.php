<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = "https://globalcomix.com/c/attack-on-titan";
$response = Illuminate\Support\Facades\Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language' => 'en-US,en;q=0.9',
])->get($url);

echo "Status: " . $response->status() . "\n";
$html = $response->body();
echo substr($html, 0, 500) . "\n";
if (strpos($html, '__REACT_QUERY_STATE__') !== false) {
    echo "REACT STATE IS PRESENT!\n";
} else {
    echo "REACT STATE MISSING!\n";
}
