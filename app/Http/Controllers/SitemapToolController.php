<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SitemapToolController extends Controller
{
    // Tunables
    private int $maxUrls   = 50000;   // Cap total URLs for big sites
    private int $maxDepth  = 6;       // Increased depth for blogs
    private int $perReqTimeout = 6;   // Seconds per HTTP request
    private int $timeBudgetSec = 300; // 5 min time budget

    public function generate(Request $request)
    {
        $startedAt = microtime(true);

        try {
            $domain = rtrim($request->input('domain', ''), '/');

            // Validate URL
            if (!filter_var($domain, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['domain' => 'Invalid URL']);
            }

            $domain = $this->normalizeUrl($domain);
            $host   = parse_url($domain, PHP_URL_HOST);

            Log::withContext(['domain' => $domain, 'host' => $host]);
            Log::info('Sitemap generation started', [
                'maxUrls' => $this->maxUrls,
                'maxDepth' => $this->maxDepth,
                'timeBudgetSec' => $this->timeBudgetSec,
            ]);

            // Crawl with limits & logging
            $urls = $this->crawlWebsite($domain, $startedAt);

            if (empty($urls)) {
                Log::warning('Crawl finished with 0 URLs');
                return back()->withErrors(['domain' => 'No URLs found or website could not be crawled.']);
            }

            // Render XML
            $xmlContent = view('sitemap-template', compact('urls'))->render();

            // Save file
            $fileName = 'sitemaps/' . md5($domain) . '-sitemap.xml';
            Storage::disk('public')->put($fileName, $xmlContent);

            Log::info('Sitemap saved', ['file' => $fileName, 'urlCount' => count($urls)]);

            // Return file for download
            return response()->download(storage_path('app/public/' . $fileName))
                ->deleteFileAfterSend(false); // keep file
        } catch (Exception $e) {
            Log::error('Sitemap generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'Something went wrong while generating the sitemap. Check logs for details.'
            ]);
        }
    }

    /**
     * Crawl using BFS with depth and time caps.
     */
    private function crawlWebsite(string $startUrl, float $startedAt): array
    {
        $host = parse_url($startUrl, PHP_URL_HOST);

        // BFS queue: [url, depth]
        $queue = [[$startUrl, 0]];
        if (!str_ends_with($startUrl, '/blogs')) {
            $queue[] = [$startUrl . '/blogs', 1];
        }



        $visited = [];
        $visited[$startUrl] = true;

        $batchSize = 10; // Number of URLs per parallel batch
        $processed = 0;

        while (!empty($queue)) {
            // Stop if time limit exceeded
            if ((microtime(true) - $startedAt) > $this->timeBudgetSec) {
                Log::warning('Stopping crawl due to time budget', [
                    'elapsedSec' => round(microtime(true) - $startedAt, 2),
                    'visited' => count($visited),
                ]);
                break;
            }

            $batch = [];
            for ($i = 0; $i < $batchSize && !empty($queue); $i++) {
                $batch[] = array_shift($queue);
            }

            // Parallel requests using HTTP pool
            $responses = Http::pool(function ($pool) use ($batch) {
                foreach ($batch as [$url, $depth]) {
                    $pool->as($url)
                        ->timeout($this->perReqTimeout)
                        ->withOptions(['verify' => false]) // Optional: disable SSL verify if needed
                        ->get($url);
                }
            });

            foreach ($batch as [$url, $depth]) {
                $response = $responses[$url] ?? null;

                // ✅ Handle connection failures or null responses
                if (!$response instanceof \Illuminate\Http\Client\Response) {
                    Log::warning('Request failed', [
                        'url' => $url,
                        'type' => is_object($response) ? get_class($response) : 'null'
                    ]);
                    continue;
                }

                // ✅ Check if response was successful and HTML content
                if (!$response->successful() || strpos(strtolower($response->header('Content-Type')), 'text/html') === false) {
                    continue;
                }

                // ✅ Extract links if within max depth
                if ($depth < $this->maxDepth) {
                    $links = $this->extractLinks($response->body(), $url);

                    foreach ($links as $link) {
                        $link = $this->normalizeUrl($link);

                        if ($this->shouldSkip($link)) continue;
                        if (!$this->sameHost($host, $link)) continue;

                        if (!isset($visited[$link])) {
                            $visited[$link] = true;
                            $queue[] = [$link, $depth + 1];

                            if (count($visited) >= $this->maxUrls) {
                                Log::warning('Reached maxUrls cap; stopping discovery', ['maxUrls' => $this->maxUrls]);
                                break 2; // Exit both loops
                            }
                        }
                    }
                }

                $processed++;
                if ($processed % 10 === 0) {
                    Log::debug('Progress', ['processedPages' => $processed, 'uniqueUrls' => count($visited)]);
                }
            }
        }

        $list = array_keys($visited);
        sort($list, SORT_STRING);

        Log::info('Crawl finished', [
            'totalUrls' => count($list),
            'elapsedSec' => round(microtime(true) - $startedAt, 2),
        ]);

        return $list;
    }



    private function httpClient()
    {
        $verify = filter_var(env('HTTP_VERIFY', true), FILTER_VALIDATE_BOOL);
        $caPath = env('HTTP_CA_BUNDLE');

        $options = [
            'verify' => $verify ? ($caPath ?: true) : false,
            'allow_redirects' => [
                'max' => 5,
                'strict' => false,
                'referer' => false,
                'track_redirects' => true,
            ],
            'headers' => [
                'User-Agent' => 'LaravelSitemapBot/1.0 (+https://yourdomain.example)',
                'Accept' => 'text/html,application/xhtml+xml',
            ],
        ];

        return Http::withOptions($options);
    }

    private function extractLinks(string $html, string $baseUrl): array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET | LIBXML_COMPACT);
        $xpath = new \DOMXPath($dom);

        $hrefs = [];
        foreach ($xpath->query('//a[@href]') as $node) {
            /** @var \DOMElement $node */
            $href = trim($node->getAttribute('href'));

            // Skip empty, hash-only, or bad links
            if (
                $href === '' || $href[0] === '#' ||
                stripos($href, 'javascript:') === 0 ||
                stripos($href, 'mailto:') === 0 ||
                stripos($href, 'tel:') === 0
            ) {
                continue;
            }

            $resolved = $this->resolveUrl($href, $baseUrl);
            $hrefs[] = $resolved;
        }
        libxml_clear_errors();

        return array_values(array_unique($hrefs));
    }


    private function resolveUrl(string $href, string $base): string
    {
        if (preg_match('#^https?://#i', $href)) return $href;
        if (strpos($href, '//') === 0) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $href;
        }
        if (strpos($href, '/') === 0) {
            $parts = parse_url($base);
            return ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . $href;
        }
        $baseDir = rtrim(dirname(parse_url($base, PHP_URL_PATH) ?: '/'), '/');
        $prefix  = preg_replace('#/+#', '/', $baseDir . '/');
        $parts   = parse_url($base);
        return ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . $prefix . $href;
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        $url = preg_replace('#(\#|%23).*$#', '', $url);

        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) return $url;

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host   = strtolower($parts['host']);
        $path   = $parts['path'] ?? '';
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');

        // Drop query string for deduplication
        return $scheme . '://' . $host . $path;
    }

    private function shouldSkip(string $url): bool
    {
        if (preg_match('#^(mailto:|tel:|javascript:)#i', $url)) return true;
        if (preg_match('#/(admin|login|register|logout)#i', $url)) return true;
        if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|rar|7z|mp3|mp4|avi|mov|wmv|docx?|xlsx?|pptx?)$#i', parse_url($url, PHP_URL_PATH) ?? '')) {
            return true;
        }
        return false;
    }

    private function sameHost(string $baseHost, string $url): bool
    {
        $linkHost = parse_url($url, PHP_URL_HOST) ?: '';
        $normalize = fn($h) => ltrim(strtolower($h), ' ');
        $stripWww  = fn($h) => preg_replace('/^www\./i', '', $h);

        return $stripWww($normalize($baseHost)) === $stripWww($normalize($linkHost));
    }
}
