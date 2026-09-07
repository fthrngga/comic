<?php
$html = file_get_contents('storage/logs/mgread_chapter.html');
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$imgNodes = $xpath->query("//img");
$images = [];
foreach ($imgNodes as $node) {
    $src = $node->getAttribute('data-src') ?: $node->getAttribute('src');
    if (strpos($src, 'data:image') === false && strpos($src, 'wp-content/uploads/2026') === false) {
        $images[] = $src;
    }
}
print_r(array_slice($images, 0, 5));
