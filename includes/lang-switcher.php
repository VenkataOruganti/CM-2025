<?php
/**
 * Language Switcher Component
 *
 * Include this in your navbar to show the language dropdown.
 *
 * Usage:
 *   <?php include __DIR__ . '/lang-switcher.php'; ?>
 */

// Get current language info
$currentLang = Lang::current();
$currentInfo = Lang::currentInfo();
$supportedLangs = Lang::getSupported();

// Get current URL for language switch links
$currentUrl = $_SERVER['REQUEST_URI'];
$urlParts = parse_url($currentUrl);
$path = $urlParts['path'] ?? '';
$query = [];
if (isset($urlParts['query'])) {
    parse_str($urlParts['query'], $query);
}
?>

<!-- Language Switcher Styles -->
<style>
.lang-switcher {
    position: relative;
    display: inline-block;
}

.lang-switcher-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.75rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    color: inherit;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.lang-switcher-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.lang-icon {
    font-size: 1rem;
}

.lang-arrow {
    font-size: 0.6rem;
    transition: transform 0.2s ease;
}

.lang-switcher.open .lang-arrow {
    transform: rotate(180deg);
}

.lang-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 160px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 1000;
    overflow: hidden;
}

.lang-switcher.open .lang-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.lang-option {
    display: flex;
    flex-direction: column;
    padding: 0.75rem 1rem;
    color: #333;
    text-decoration: none;
    transition: background 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.lang-option:last-child {
    border-bottom: none;
}

.lang-option:hover {
    background: #f7f7f7;
}

.lang-option.active {
    background: linear-gradient(135deg, #667eea15, #764ba215);
}

.lang-option.active::before {
    content: '✓';
    position: absolute;
    right: 1rem;
    color: #667eea;
    font-weight: bold;
}

.lang-native {
    font-size: 0.95rem;
    font-weight: 500;
    color: #333;
}

.lang-english {
    font-size: 0.75rem;
    color: #888;
    margin-top: 0.15rem;
}

/* Dark navbar variant */
.navbar-dark .lang-switcher-btn {
    color: white;
}

.navbar-dark .lang-switcher-btn:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* Mobile responsive - styles handled by header.php when inside nav-links */
</style>

<!-- Language Switcher HTML -->
<div class="lang-switcher" id="langSwitcher">
    <button type="button" class="lang-switcher-btn" id="langSwitcherBtn">
        <span class="lang-icon">🌐</span>
        <span class="lang-name"><?php echo $currentInfo['native']; ?></span>
        <span class="lang-arrow">▼</span>
    </button>
    <div class="lang-menu" id="langMenu">
        <?php foreach ($supportedLangs as $code => $info):
            // Build URL with lang parameter
            $query['lang'] = $code;
            $langUrl = $path . '?' . http_build_query($query);
            $activeClass = ($code === $currentLang) ? ' active' : '';
        ?>
        <a href="<?php echo htmlspecialchars($langUrl); ?>" class="lang-option<?php echo $activeClass; ?>" data-lang="<?php echo $code; ?>">
            <span class="lang-native"><?php echo $info['native']; ?></span>
            <span class="lang-english"><?php echo $info['name']; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Language Switcher JavaScript - iOS Touch Optimized -->
<script>
(function() {
    var switcher = document.getElementById('langSwitcher');
    var btn = document.getElementById('langSwitcherBtn');
    var menu = document.getElementById('langMenu');

    if (!switcher || !btn) return;

    // iOS-friendly touch handler for button
    var touchStarted = false;
    var touchMoved = false;

    btn.addEventListener('touchstart', function(e) {
        touchStarted = true;
        touchMoved = false;
        this.classList.add('touch-active');
    }, { passive: true });

    btn.addEventListener('touchmove', function(e) {
        touchMoved = true;
        this.classList.remove('touch-active');
    }, { passive: true });

    btn.addEventListener('touchend', function(e) {
        this.classList.remove('touch-active');
        if (touchStarted && !touchMoved) {
            e.preventDefault();
            e.stopPropagation();
            switcher.classList.toggle('open');
        }
        touchStarted = false;
    }, { passive: false });

    // Click for non-touch devices
    btn.addEventListener('click', function(e) {
        if (!('ontouchstart' in window)) {
            e.stopPropagation();
            switcher.classList.toggle('open');
        }
    });

    // Handle language option touches
    var options = menu.querySelectorAll('.lang-option');
    options.forEach(function(option) {
        var optTouchStarted = false;
        var optTouchMoved = false;
        var href = option.getAttribute('href');

        option.addEventListener('touchstart', function(e) {
            optTouchStarted = true;
            optTouchMoved = false;
            this.classList.add('touch-active');
        }, { passive: true });

        option.addEventListener('touchmove', function(e) {
            optTouchMoved = true;
            this.classList.remove('touch-active');
        }, { passive: true });

        option.addEventListener('touchend', function(e) {
            this.classList.remove('touch-active');
            if (optTouchStarted && !optTouchMoved && href) {
                e.preventDefault();
                switcher.classList.remove('open');
                // Navigate after brief delay for visual feedback
                setTimeout(function() {
                    window.location.href = href;
                }, 100);
            }
            optTouchStarted = false;
        }, { passive: false });
    });

    // Close menu when clicking/touching outside
    document.addEventListener('click', function(e) {
        if (!switcher.contains(e.target)) {
            switcher.classList.remove('open');
        }
    });

    document.addEventListener('touchend', function(e) {
        if (!switcher.contains(e.target)) {
            switcher.classList.remove('open');
        }
    }, { passive: true });

    // Close menu on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            switcher.classList.remove('open');
        }
    });
})();
</script>
