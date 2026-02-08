<?php
/**
 * =============================================================================
 * BLOG POST VIEWER PAGE
 * =============================================================================
 *
 * Displays a single blog post with full content.
 * Supports multilingual content from JSON files.
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';
require_once __DIR__ . '/../includes/blog-helper.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Get current language
$currentLang = Lang::current();

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

if (!$slug) {
    header('Location: blog.php');
    exit;
}

// Get the post
$post = Blog::getPost($slug, $currentLang);

if (!$post) {
    // Post not found
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Post Not Found';
    $metaDescription = 'The requested blog post could not be found.';
} else {
    $pageTitle = $post['title'];
    $metaDescription = $post['meta_description'] ?? $post['excerpt'] ?? Blog::generateExcerpt($post['content'] ?? '', 160);
}

// Get available translations
$translations = Blog::getAvailableTranslations($slug);

// Get related posts (same category)
$relatedPosts = [];
if ($post && !empty($post['category'])) {
    $allPosts = Blog::getPostsByCategory($post['category'], $currentLang);
    $relatedPosts = array_filter($allPosts, function($p) use ($slug) {
        return $p['slug'] !== $slug;
    });
    $relatedPosts = array_slice($relatedPosts, 0, 3);
}

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$metaKeywords = implode(', ', $post['tags'] ?? []) . ', tailoring tips, pattern making';
$activePage = 'blog';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = '../index.php';
$navBase = '../';

// Get current user info for header
if ($isLoggedIn) {
    require_once __DIR__ . '/../config/auth.php';
    $currentUser = getCurrentUser();
}

// Page-specific styles
$additionalStyles = <<<'CSS'
    .blog-post-section {
        padding: 6rem 2rem 4rem;
        min-height: 100vh;
        position: relative;
    }

    .blog-post-container {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .blog-post-header {
        margin-bottom: 2rem;
    }

    .blog-post-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #718096;
        text-decoration: none;
        font-size: 0.9rem;
        margin-bottom: 2rem;
        transition: color 0.3s ease;
    }

    .blog-post-back:hover {
        color: #8B7BA8;
    }

    .blog-post-category {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #8B7BA8;
        margin-bottom: 1rem;
    }

    .blog-post-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.75rem;
        font-weight: 300;
        color: #2D3748;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .blog-post-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        color: #A0AEC0;
        font-size: 0.9rem;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(177, 156, 217, 0.2);
    }

    .blog-post-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .translation-notice {
        background: rgba(237, 137, 54, 0.1);
        border: 1px solid rgba(237, 137, 54, 0.3);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #ED8936;
        font-size: 0.9rem;
    }

    .translation-switcher {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .translation-btn {
        padding: 0.4rem 0.75rem;
        background: #FFFFFF;
        border: 1px solid rgba(177, 156, 217, 0.3);
        border-radius: 4px;
        color: #718096;
        font-size: 0.8rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .translation-btn:hover {
        background: rgba(177, 156, 217, 0.1);
        border-color: #B19CD9;
        color: #8B7BA8;
    }

    .translation-btn.active {
        background: #8B7BA8;
        border-color: #8B7BA8;
        color: #FFFFFF;
    }

    .blog-post-image {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        object-position: center;
        background: #f8f8f8;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(177, 156, 217, 0.15);
    }

    .blog-post-content {
        color: #4A5568;
        font-size: 1.1rem;
        line-height: 1.8;
    }

    .blog-post-content h1,
    .blog-post-content h2,
    .blog-post-content h3 {
        font-family: 'Cormorant Garamond', serif;
        color: #2D3748;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .blog-post-content h2 {
        font-size: 1.75rem;
        font-weight: 400;
    }

    .blog-post-content h3 {
        font-size: 1.4rem;
        font-weight: 400;
    }

    .blog-post-content p {
        margin-bottom: 1.5rem;
    }

    .blog-post-content ul,
    .blog-post-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }

    .blog-post-content li {
        margin-bottom: 0.5rem;
    }

    .blog-post-content a {
        color: #8B7BA8;
        text-decoration: underline;
    }

    .blog-post-content strong {
        color: #2D3748;
    }

    .blog-post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(177, 156, 217, 0.2);
    }

    .blog-post-tag {
        padding: 0.4rem 0.75rem;
        background: rgba(177, 156, 217, 0.1);
        border-radius: 4px;
        color: #8B7BA8;
        font-size: 0.8rem;
    }

    .related-posts {
        margin-top: 4rem;
        padding-top: 3rem;
        border-top: 1px solid rgba(177, 156, 217, 0.2);
    }

    .related-posts-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.75rem;
        font-weight: 400;
        color: #2D3748;
        margin-bottom: 1.5rem;
    }

    .related-posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .related-post-card {
        background: #FFFFFF;
        border: 1px solid rgba(177, 156, 217, 0.2);
        border-radius: 8px;
        padding: 1.25rem;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(177, 156, 217, 0.1);
    }

    .related-post-card:hover {
        border-color: rgba(177, 156, 217, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(177, 156, 217, 0.2);
    }

    .related-post-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem;
        color: #2D3748;
        margin-bottom: 0.5rem;
    }

    .related-post-date {
        font-size: 0.8rem;
        color: #A0AEC0;
    }

    .not-found {
        text-align: center;
        padding: 4rem 2rem;
    }

    .not-found h1 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.5rem;
        color: #2D3748;
        margin-bottom: 1rem;
    }

    .not-found p {
        color: #718096;
        margin-bottom: 2rem;
    }

    .not-found a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #8B7BA8;
        text-decoration: none;
    }

    .not-found a:hover {
        color: #6B5B88;
    }

    @media (max-width: 768px) {
        .blog-post-section {
            padding: 5rem 1rem 3rem;
        }

        .blog-post-title {
            font-size: 2rem;
        }

        .blog-post-content {
            font-size: 1rem;
        }
    }
CSS;

include __DIR__ . '/../includes/header.php';
?>

    <!-- Blog Post Section -->
    <section class="blog-post-section">
        <div class="blog-post-container">
            <?php if (!$post): ?>
            <!-- Post Not Found -->
            <div class="not-found">
                <i data-lucide="file-x" style="width: 64px; height: 64px; color: rgba(255,255,255,0.2); margin-bottom: 1rem;"></i>
                <h1><?php _e('blog.post_not_found'); ?></h1>
                <p><?php _e('blog.post_not_found_desc'); ?></p>
                <a href="blog.php">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    <?php _e('blog.back_to_blog'); ?>
                </a>
            </div>
            <?php else: ?>
            <!-- Blog Post Header -->
            <header class="blog-post-header">
                <a href="blog.php" class="blog-post-back">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    <?php _e('blog.back_to_blog'); ?>
                </a>

                <span class="blog-post-category"><?php echo htmlspecialchars($post['category'] ?? 'General'); ?></span>

                <h1 class="blog-post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

                <div class="blog-post-meta">
                    <span class="blog-post-meta-item">
                        <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                        <?php echo Blog::formatDate($post['published_date'] ?? ''); ?>
                    </span>
                    <span class="blog-post-meta-item">
                        <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                        <?php echo htmlspecialchars($post['author'] ?? 'CuttingMaster Team'); ?>
                    </span>
                    <?php if (!empty($post['read_time'])): ?>
                    <span class="blog-post-meta-item">
                        <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
                        <?php echo htmlspecialchars($post['read_time']); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (!empty($post['translation_missing'])): ?>
            <div class="translation-notice">
                <i data-lucide="info" style="width: 20px; height: 20px;"></i>
                <span><?php _e('blog.translation_not_available'); ?></span>
            </div>
            <?php endif; ?>

            <?php if (count($translations) > 1): ?>
            <div class="translation-switcher">
                <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-right: 0.5rem;"><?php _e('blog.read_in'); ?>:</span>
                <?php foreach ($translations as $code => $info): ?>
                <a href="?slug=<?php echo urlencode($slug); ?>&lang=<?php echo $code; ?>"
                   class="translation-btn <?php echo $post['lang'] === $code ? 'active' : ''; ?>">
                    <?php echo $info['native']; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($post['featured_image'])): ?>
            <img src="../<?php echo htmlspecialchars($post['featured_image']); ?>"
                 alt="<?php echo htmlspecialchars($post['title']); ?>"
                 class="blog-post-image">
            <?php endif; ?>

            <!-- Blog Post Content -->
            <article class="blog-post-content">
                <?php echo Blog::parseMarkdown($post['content'] ?? ''); ?>
            </article>

            <?php if (!empty($post['tags'])): ?>
            <div class="blog-post-tags">
                <?php foreach ($post['tags'] as $tag): ?>
                <span class="blog-post-tag">#<?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatedPosts)): ?>
            <div class="related-posts">
                <h2 class="related-posts-title"><?php _e('blog.related_posts'); ?></h2>
                <div class="related-posts-grid">
                    <?php foreach ($relatedPosts as $related): ?>
                    <a href="blog-post.php?slug=<?php echo urlencode($related['slug']); ?>" class="related-post-card">
                        <h3 class="related-post-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                        <span class="related-post-date"><?php echo Blog::formatDate($related['published_date']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <script>
        lucide.createIcons();
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
