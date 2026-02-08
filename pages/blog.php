<?php
/**
 * =============================================================================
 * BLOG INDEX PAGE
 * =============================================================================
 *
 * Displays all blog posts with filtering by category.
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

// Get category filter
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : null;

// Get all posts
$posts = Blog::getAllPosts($currentLang);

// Filter by category if specified
if ($categoryFilter) {
    $posts = array_filter($posts, function($post) use ($categoryFilter) {
        return strtolower($post['category']) === strtolower($categoryFilter);
    });
    $posts = array_values($posts); // Re-index array
}

// Get all categories for filter menu
$categories = Blog::getCategories($currentLang);

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$pageTitle = __('blog.page_title');
$metaDescription = __('blog.meta_description');
$metaKeywords = 'tailoring tips, pattern making guide, blouse stitching, saree blouse tips, tailoring blog India, sewing tutorials';
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
    .blog-section {
        padding: 6rem 2rem 4rem;
        min-height: 100vh;
        position: relative;
    }

    .blog-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .blog-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .blog-tag {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #8B7BA8;
        margin-bottom: 1rem;
    }

    .blog-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 3rem;
        font-weight: 300;
        color: #2D3748;
        margin-bottom: 1rem;
    }

    .blog-description {
        color: #718096;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .blog-filters {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 3rem;
    }

    .filter-btn {
        padding: 0.5rem 1.25rem;
        background: #FFFFFF;
        border: 1px solid rgba(177, 156, 217, 0.3);
        border-radius: 20px;
        color: #718096;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .filter-btn:hover {
        background: rgba(177, 156, 217, 0.1);
        border-color: #B19CD9;
        color: #8B7BA8;
    }

    .filter-btn.active {
        background: #8B7BA8;
        border-color: #8B7BA8;
        color: #FFFFFF;
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
    }

    .blog-card {
        background: #FFFFFF;
        border: 1px solid rgba(177, 156, 217, 0.2);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(177, 156, 217, 0.1);
    }

    .blog-card:hover {
        transform: translateY(-4px);
        border-color: rgba(177, 156, 217, 0.4);
        box-shadow: 0 10px 40px rgba(177, 156, 217, 0.2);
    }

    .blog-card-image {
        width: 100%;
        height: 200px;
        object-fit: contain;
        object-position: center;
        background: #f8f8f8;
    }

    .blog-card-content {
        padding: 1.5rem;
    }

    .blog-card-category {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #8B7BA8;
        margin-bottom: 0.75rem;
    }

    .blog-card-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        font-weight: 400;
        color: #2D3748;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .blog-card-title a {
        color: inherit;
        text-decoration: none;
    }

    .blog-card-title a:hover {
        color: #8B7BA8;
    }

    .blog-card-excerpt {
        color: #718096;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .blog-card-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
        color: #A0AEC0;
    }

    .blog-card-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .blog-card-author {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .translation-badge {
        display: inline-block;
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        background: rgba(237, 137, 54, 0.15);
        color: #ED8936;
        border-radius: 4px;
        margin-left: 0.5rem;
    }

    .blog-empty {
        text-align: center;
        padding: 4rem 2rem;
        color: #A0AEC0;
    }

    .blog-empty i {
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .read-more {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #8B7BA8;
        font-size: 0.85rem;
        text-decoration: none;
        transition: gap 0.3s ease;
    }

    .read-more:hover {
        gap: 0.75rem;
        color: #6B5B88;
    }

    @media (max-width: 768px) {
        .blog-section {
            padding: 5rem 1rem 3rem;
        }

        .blog-title {
            font-size: 2rem;
        }

        .blog-grid {
            grid-template-columns: 1fr;
        }
    }
CSS;

include __DIR__ . '/../includes/header.php';
?>

    <!-- Blog Section -->
    <section class="blog-section">
        <div class="blog-container">
            <div class="blog-header">
                <p class="blog-tag"><?php _e('blog.tag'); ?></p>
                <h1 class="blog-title"><?php _e('blog.title'); ?></h1>
                <p class="blog-description"><?php _e('blog.description'); ?></p>
            </div>

            <?php if (!empty($categories)): ?>
            <div class="blog-filters">
                <a href="blog.php" class="filter-btn <?php echo $categoryFilter === null ? 'active' : ''; ?>">
                    <?php _e('blog.all_posts'); ?>
                </a>
                <?php foreach ($categories as $cat => $count): ?>
                <a href="blog.php?category=<?php echo urlencode($cat); ?>"
                   class="filter-btn <?php echo $categoryFilter === $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?> (<?php echo $count; ?>)
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($posts)): ?>
            <div class="blog-empty">
                <i data-lucide="file-text" style="width: 48px; height: 48px;"></i>
                <h3><?php _e('blog.no_posts'); ?></h3>
                <p><?php _e('blog.no_posts_desc'); ?></p>
            </div>
            <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                    <?php if (!empty($post['featured_image'])): ?>
                    <img src="../<?php echo htmlspecialchars($post['featured_image']); ?>"
                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                         class="blog-card-image">
                    <?php else: ?>
                    <div class="blog-card-image" style="display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="image" style="width: 48px; height: 48px; opacity: 0.2;"></i>
                    </div>
                    <?php endif; ?>

                    <div class="blog-card-content">
                        <span class="blog-card-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <?php if (!empty($post['translation_missing'])): ?>
                        <span class="translation-badge"><?php _e('blog.english_only'); ?></span>
                        <?php endif; ?>

                        <h2 class="blog-card-title">
                            <a href="blog-post.php?slug=<?php echo urlencode($post['slug']); ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>

                        <p class="blog-card-excerpt">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </p>

                        <div class="blog-card-meta">
                            <span class="blog-card-date">
                                <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                <?php echo Blog::formatDate($post['published_date']); ?>
                            </span>
                            <a href="blog-post.php?slug=<?php echo urlencode($post['slug']); ?>" class="read-more">
                                <?php _e('blog.read_more'); ?>
                                <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
