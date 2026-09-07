<?php
$html = file_get_contents('storage/logs/mgread_action.html');
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$mangaNodes = $xpath->query("//div[contains(@class, 'story-cover-wrap')]//a");

$mangaLinks = [];
foreach ($mangaNodes as $node) {
    $href = $node->getAttribute('href');
    $mangaLinks[] = $href;
}
print_r(array_slice($mangaLinks, 0, 10));
