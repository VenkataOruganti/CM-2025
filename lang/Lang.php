<?php
/**
 * =============================================================================
 * LANGUAGE CLASS - Multi-lingual Support for CuttingMaster
 * =============================================================================
 *
 * Handles language detection, loading, and translation retrieval.
 *
 * Supported Languages:
 * - en: English (default/fallback)
 * - hi: Hindi (हिंदी)
 * - te: Telugu (తెలుగు)
 *
 * Usage:
 *   Lang::init();
 *   echo __('nav.pattern_studio');  // "Pattern Studio" or translated
 *   echo __('messages.welcome', ['name' => 'Raju']);  // "Welcome, Raju!"
 *
 * @author CM-2025
 * @date January 2026
 */

class Lang {

    /** @var string Current language code */
    private static $currentLang = 'en';

    /** @var array Loaded translations */
    private static $translations = [];

    /** @var array Fallback translations (English) */
    private static $fallback = [];

    /** @var array Supported languages */
    private static $supported = [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिंदी', 'dir' => 'ltr'],
        'te' => ['name' => 'Telugu', 'native' => 'తెలుగు', 'dir' => 'ltr']
    ];

    /** @var array Missing translation log */
    private static $missing = [];

    /** @var bool Initialization flag */
    private static $initialized = false;

    /**
     * Initialize the language system
     *
     * Priority:
     * 1. URL parameter (?lang=te)
     * 2. Cookie (cm_lang)
     * 3. Browser Accept-Language header
     * 4. Default (English)
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        // Load fallback (English) first
        self::$fallback = self::loadFile('en');

        // Determine language
        $lang = self::detectLanguage();
        self::setLanguage($lang);

        self::$initialized = true;
    }

    /**
     * Detect the preferred language
     */
    private static function detectLanguage() {
        // 1. Check URL parameter
        if (isset($_GET['lang']) && self::isSupported($_GET['lang'])) {
            return $_GET['lang'];
        }

        // 2. Check cookie
        if (isset($_COOKIE['cm_lang']) && self::isSupported($_COOKIE['cm_lang'])) {
            return $_COOKIE['cm_lang'];
        }

        // 3. Check browser Accept-Language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($browserLangs as $browserLang) {
                $lang = substr(trim($browserLang), 0, 2);
                if (self::isSupported($lang)) {
                    return $lang;
                }
            }
        }

        // 4. Default to English
        return 'en';
    }

    /**
     * Set the current language and load translations
     */
    public static function setLanguage($lang) {
        if (!self::isSupported($lang)) {
            $lang = 'en';
        }

        self::$currentLang = $lang;

        // Load translations for this language
        if ($lang !== 'en') {
            self::$translations = self::loadFile($lang);
        } else {
            self::$translations = self::$fallback;
        }

        // Set cookie for 30 days
        if (!headers_sent()) {
            setcookie('cm_lang', $lang, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
    }

    /**
     * Load a language file
     */
    private static function loadFile($lang) {
        $file = __DIR__ . '/' . $lang . '.json';

        if (!file_exists($file)) {
            error_log("Language file not found: $file");
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON parse error in $file: " . json_last_error_msg());
            return [];
        }

        // Remove meta section
        unset($data['_meta']);

        // Flatten nested arrays for easy access
        return self::flatten($data);
    }

    /**
     * Flatten nested array to dot notation
     * ['nav' => ['home' => 'Home']] becomes ['nav.home' => 'Home']
     */
    private static function flatten($array, $prefix = '') {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Get a translation
     *
     * @param string $key Dot-notation key (e.g., 'nav.home')
     * @param array $params Replacement parameters (e.g., ['name' => 'Raju'])
     * @return string Translated string or key if not found
     */
    public static function get($key, $params = []) {
        // Try current language
        if (isset(self::$translations[$key])) {
            return self::replacePlaceholders(self::$translations[$key], $params);
        }

        // Try fallback (English)
        if (isset(self::$fallback[$key])) {
            self::logMissing($key);
            return self::replacePlaceholders(self::$fallback[$key], $params);
        }

        // Key not found anywhere
        self::logMissing($key, true);
        return $key;
    }

    /**
     * Replace {placeholder} with values
     */
    private static function replacePlaceholders($text, $params) {
        foreach ($params as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }

    /**
     * Log missing translation
     */
    private static function logMissing($key, $notInFallback = false) {
        if (!isset(self::$missing[$key])) {
            self::$missing[$key] = [
                'lang' => self::$currentLang,
                'not_in_fallback' => $notInFallback,
                'time' => date('Y-m-d H:i:s')
            ];

            // Log to error log in development
            if (defined('CM_DEBUG') && CM_DEBUG) {
                $msg = $notInFallback
                    ? "Missing translation key: $key"
                    : "Missing translation for '" . self::$currentLang . "': $key";
                error_log($msg);
            }
        }
    }

    /**
     * Check if a language is supported
     */
    public static function isSupported($lang) {
        return isset(self::$supported[$lang]);
    }

    /**
     * Get current language code
     */
    public static function current() {
        return self::$currentLang;
    }

    /**
     * Get current language info
     */
    public static function currentInfo() {
        return self::$supported[self::$currentLang];
    }

    /**
     * Get all supported languages
     */
    public static function getSupported() {
        return self::$supported;
    }

    /**
     * Get missing translations (for debugging)
     */
    public static function getMissing() {
        return self::$missing;
    }

    /**
     * Get language direction (ltr/rtl)
     */
    public static function getDirection() {
        return self::$supported[self::$currentLang]['dir'];
    }

    /**
     * Generate language switcher HTML
     */
    public static function renderSwitcher($class = 'lang-switcher') {
        $current = self::$currentLang;
        $currentInfo = self::$supported[$current];

        $html = '<div class="' . $class . '">';
        $html .= '<button class="lang-switcher-btn" onclick="toggleLangMenu()">';
        $html .= '<span class="lang-icon">🌐</span>';
        $html .= '<span class="lang-name">' . $currentInfo['native'] . '</span>';
        $html .= '<span class="lang-arrow">▼</span>';
        $html .= '</button>';
        $html .= '<div class="lang-menu" id="langMenu">';

        foreach (self::$supported as $code => $info) {
            $activeClass = ($code === $current) ? ' active' : '';
            $html .= '<a href="?lang=' . $code . '" class="lang-option' . $activeClass . '">';
            $html .= '<span class="lang-native">' . $info['native'] . '</span>';
            $html .= '<span class="lang-english">' . $info['name'] . '</span>';
            $html .= '</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get all translations as JSON for JavaScript
     */
    public static function toJson() {
        return json_encode(self::$translations, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Global helper function for translations
 *
 * Usage: echo __('nav.home');
 *        echo __('messages.welcome', ['name' => 'Raju']);
 */
if (!function_exists('__')) {
    function __($key, $params = []) {
        return Lang::get($key, $params);
    }
}

/**
 * Echo translation directly
 *
 * Usage: _e('nav.home');
 */
if (!function_exists('_e')) {
    function _e($key, $params = []) {
        echo Lang::get($key, $params);
    }
}
