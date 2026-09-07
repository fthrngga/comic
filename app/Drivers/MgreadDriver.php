<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class MgreadDriver implements ComicDriverInterface
{
    /**
     * Helper curl untuk fallback saat Guzzle Http di-block (meskipun mgread.io harusnya tidak butuh)
     */
    private function fetchHtml($url)
    {
        // Gunakan system curl
        $output = shell_exec("curl -s -L \"$url\"");
        return $output;
    }

    public function getMangaDetails(string $url): array
    {
        try {
            $html = $this->fetchHtml($url);
            if (!$html || trim($html) === '') {
                throw new \Exception("Gagal mengambil data dari URL (kosong): $url");
            }

            // Gunakan DOMDocument untuk parsing murni tanpa crawler pihak ketiga agar ringan
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            // Ekstrak Judul
            $titleNode = $xpath->query("//h1[contains(@id, 'manga-title')]");
            if ($titleNode->length === 0) {
                // Fallback pencarian judul
                $titleNode = $xpath->query("//title");
                if ($titleNode->length === 0) {
                    throw new \Exception("Gagal menemukan judul komik");
                }
                $title = str_replace([' - Mgread.io', ' ['], ['', ''], $titleNode->item(0)->textContent);
            } else {
                $title = trim($titleNode->item(0)->textContent);
            }

            // Ekstrak Sinopsis
            $synopsisNode = $xpath->query("//div[@id='manga-description']//p");
            $synopsis = $synopsisNode->length > 0 ? trim($synopsisNode->item(0)->textContent) : 'Tidak ada sinopsis.';

            // Ekstrak Cover URL
            $coverNode = $xpath->query("//div[contains(@class, 'story-cover-wrap')]//img");
            $coverUrl = null;
            if ($coverNode->length > 0) {
                $coverUrl = $coverNode->item(0)->getAttribute('src');
            }

            // Status 
            $statusNode = $xpath->query("//span[@id='manga-status']");
            $status = $statusNode->length > 0 ? strtolower(trim($statusNode->item(0)->textContent)) : 'ongoing';

            // Ekstrak Chapters
            $chapterNodes = $xpath->query("//div[contains(@class, 'chapter-item')]//a");
            $chapters = [];
            
            // Loop secara mundur agar chapter terlama diproses lebih dulu atau sesuaikan dengan urutan aslinya
            foreach ($chapterNodes as $node) {
                $chapterHref = $node->getAttribute('href');
                $chapterTitleNode = $xpath->query(".//h3", $node);
                $chapterTitleFull = $chapterTitleNode->length > 0 ? trim($chapterTitleNode->item(0)->textContent) : 'Chapter ?';
                
                // Biasanya format "Nama Komik - Chapter 123"
                preg_match('/Chapter\s*([\d\.]+)/i', $chapterTitleFull, $matches);
                $chapterNumber = isset($matches[1]) ? (float) $matches[1] : 0;

                $chapters[] = [
                    'chapter_number' => $chapterNumber,
                    'title' => $chapterTitleFull,
                    'url' => $chapterHref
                ];
            }

            return [
                'title' => $title,
                'synopsis' => $synopsis,
                'type' => 'manga', // mgread fokus ke manga, manhua, manhwa
                'language' => 'en',
                'status' => $status,
                'cover_url' => $coverUrl,
                'chapters' => array_reverse($chapters) // Reverse karena biasanya urut dari terbaru ke terlama
            ];
            
        } catch (\Exception $e) {
            Log::error("MgreadDriver Error (Details): " . $e->getMessage());
            throw $e;
        }
    }

    public function getChapterImages(string $mangaId, string $chapterId = ''): array
    {
        $url = $mangaId; // Untuk mgread, kita hanya memakai URL penuh yang di-pass di parameter pertama
        try {
            $html = $this->fetchHtml($url);
            if (!$html || trim($html) === '') {
                throw new \Exception("Gagal mengambil data chapter: $url");
            }

            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            // Ekstrak images (mgread menggunakan data-original-src atau src biasa)
            $imgNodes = $xpath->query("//img");
            $images = [];

            foreach ($imgNodes as $node) {
                // Prioritaskan original-src untuk lazy loading
                $src = $node->getAttribute('data-original-src');
                if (!$src) {
                    $src = $node->getAttribute('data-src');
                }
                if (!$src) {
                    $src = $node->getAttribute('src');
                }

                if ($src && strpos($src, 'data:image') === false && strpos($src, 'wp-content/uploads/202') === false) {
                    $images[] = $src;
                }
            }

            if (empty($images)) {
                throw new \Exception("Gambar chapter tidak ditemukan");
            }

            return $images;
        } catch (\Exception $e) {
            Log::error("MgreadDriver Error (Images): " . $e->getMessage());
            throw $e;
        }
    }
}
