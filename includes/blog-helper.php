<?php
/**
 * =============================================================================
 * BLOG HELPER FUNCTIONS
 * =============================================================================
 *
 * Helper functions for loading and managing multilingual blog posts.
 * Blog content is stored in JSON files for easy editing by translators.
 *
 * Structure:
 *   lang/blog/en/post-slug.json
 *   lang/blog/hi/post-slug.json
 *   lang/blog/te/post-slug.json
 *
 * @author CuttingMaster
 * @date January 2026
 */

class Blog {

    /** @var string Base path for blog files */
    private static $basePath = null;

    /** @var array Cached posts index */
    private static $postsIndex = null;

    /**
     * Get the base path for blog files
     */
    private static function getBasePath() {
        if (self::$basePath === null) {
            self::$basePath = dirname(__DIR__) . '/lang/blog';
        }
        return self::$basePath;
    }

    /**
     * Get a single blog post by slug
     *
     * @param string $slug Post slug (filename without .json)
     * @param string|null $lang Language code (defaults to current language)
     * @return array|null Post data or null if not found
     */
    public static function getPost($slug, $lang = null) {
        if ($lang === null) {
            $lang = Lang::current();
        }

        // Sanitize slug
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

        // Try requested language first
        $file = self::getBasePath() . '/' . $lang . '/' . $slug . '.json';

        if (file_exists($file)) {
            $post = self::loadPostFile($file);
            if ($post) {
                $post['slug'] = $slug;
                $post['lang'] = $lang;
                $post['is_translation'] = ($lang !== 'en');
                return $post;
            }
        }

        // Fallback to English
        if ($lang !== 'en') {
            $fallbackFile = self::getBasePath() . '/en/' . $slug . '.json';
            if (file_exists($fallbackFile)) {
                $post = self::loadPostFile($fallbackFile);
                if ($post) {
                    $post['slug'] = $slug;
                    $post['lang'] = 'en';
                    $post['is_translation'] = false;
                    $post['translation_missing'] = true;
                    return $post;
                }
            }
        }

        return null;
    }

    /**
     * Load and parse a post JSON file
     */
    private static function loadPostFile($file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Blog JSON parse error in $file: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Get all posts for a language
     *
     * @param string|null $lang Language code
     * @param bool $publishedOnly Only return published posts
     * @return array List of posts (metadata only, not full content)
     */
    public static function getAllPosts($lang = null, $publishedOnly = true) {
        if ($lang === null) {
            $lang = Lang::current();
        }

        $posts = [];
        $langPath = self::getBasePath() . '/' . $lang;
        $enPath = self::getBasePath() . '/en';

        // Get all English posts first (as the master list)
        $englishPosts = [];
        if (is_dir($enPath)) {
            foreach (glob($enPath . '/*.json') as $file) {
                $slug = basename($file, '.json');
                $post = self::loadPostFile($file);
                if ($post) {
                    $englishPosts[$slug] = $post;
                }
            }
        }

        // Now get posts in requested language
        foreach ($englishPosts as $slug => $enPost) {
            $post = self::getPost($slug, $lang);

            if ($post) {
                // Skip unpublished posts
                if ($publishedOnly && isset($post['status']) && $post['status'] !== 'published') {
                    continue;
                }

                // Don't include full content in list
                $posts[] = [
                    'slug' => $slug,
                    'title' => $post['title'] ?? 'Untitled',
                    'excerpt' => $post['excerpt'] ?? self::generateExcerpt($post['content'] ?? ''),
                    'featured_image' => $post['featured_image'] ?? null,
                    'author' => $post['author'] ?? 'CuttingMaster Team',
                    'published_date' => $post['published_date'] ?? null,
                    'category' => $post['category'] ?? 'General',
                    'tags' => $post['tags'] ?? [],
                    'lang' => $post['lang'],
                    'translation_missing' => $post['translation_missing'] ?? false
                ];
            }
        }

        // Sort by published date (newest first)
        usort($posts, function($a, $b) {
            $dateA = strtotime($a['published_date'] ?? '1970-01-01');
            $dateB = strtotime($b['published_date'] ?? '1970-01-01');
            return $dateB - $dateA;
        });

        return $posts;
    }

    /**
     * Generate excerpt from content
     */
    private static function generateExcerpt($content, $length = 160) {
        // Remove markdown formatting
        $text = strip_tags($content);
        $text = preg_replace('/#{1,6}\s*/', '', $text);
        $text = preg_replace('/\*{1,2}([^*]+)\*{1,2}/', '$1', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    /**
     * Parse Markdown content to HTML
     * Basic markdown parser for blog content
     */
    public static function parseMarkdown($content) {
        if (empty($content)) {
            return '';
        }

        // Headers
        $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $content);

        // Bold and italic
        $content = preg_replace('/\*\*\*([^*]+)\*\*\*/', '<strong><em>$1</em></strong>', $content);
        $content = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $content);

        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $content);

        // Lists
        $content = preg_replace('/^- (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $content);

        // Numbered lists
        $content = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $content);

        // Paragraphs
        $content = preg_replace('/\n\n+/', '</p><p>', $content);
        $content = '<p>' . $content . '</p>';

        // Clean up empty paragraphs
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        $content = preg_replace('/<p>\s*(<h[1-6]>)/', '$1', $content);
        $content = preg_replace('/(<\/h[1-6]>)\s*<\/p>/', '$1', $content);
        $content = preg_replace('/<p>\s*(<ul>)/', '$1', $content);
        $content = preg_replace('/(<\/ul>)\s*<\/p>/', '$1', $content);

        return $content;
    }

    /**
     * Get available translations for a post
     */
    public static function getAvailableTranslations($slug) {
        $translations = [];
        $supported = Lang::getSupported();

        foreach ($supported as $code => $info) {
            $file = self::getBasePath() . '/' . $code . '/' . $slug . '.json';
            if (file_exists($file)) {
                $translations[$code] = $info;
            }
        }

        return $translations;
    }

    /**
     * Get posts by category
     */
    public static function getPostsByCategory($category, $lang = null) {
        $allPosts = self::getAllPosts($lang);
        return array_filter($allPosts, function($post) use ($category) {
            return strtolower($post['category']) === strtolower($category);
        });
    }

    /**
     * Get all categories with post counts
     */
    public static function getCategories($lang = null) {
        $allPosts = self::getAllPosts($lang);
        $categories = [];

        foreach ($allPosts as $post) {
            $cat = $post['category'] ?? 'General';
            if (!isset($categories[$cat])) {
                $categories[$cat] = 0;
            }
            $categories[$cat]++;
        }

        arsort($categories);
        return $categories;
    }

    /**
     * Format date for display
     */
    public static function formatDate($date, $lang = null) {
        if (empty($date)) {
            return '';
        }

        if ($lang === null) {
            $lang = Lang::current();
        }

        $timestamp = strtotime($date);

        // Format based on language
        switch ($lang) {
            case 'hi':
                // Hindi date format
                $months = ['जनवरी', 'फरवरी', 'मार्च', 'अप्रैल', 'मई', 'जून',
                          'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर', 'नवंबर', 'दिसंबर'];
                $month = $months[date('n', $timestamp) - 1];
                return date('j', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp);

            case 'te':
                // Telugu date format
                $months = ['జనవరి', 'ఫిబ్రవరి', 'మార్చి', 'ఏప్రిల్', 'మే', 'జూన్',
                          'జూలై', 'ఆగస్టు', 'సెప్టెంబర్', 'అక్టోబర్', 'నవంబర్', 'డిసెంబర్'];
                $month = $months[date('n', $timestamp) - 1];
                return date('j', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp);

            default:
                // English format
                return date('F j, Y', $timestamp);
        }
    }
}
