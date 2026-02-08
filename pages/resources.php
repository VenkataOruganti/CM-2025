<?php
/**
 * =============================================================================
 * RESOURCES PAGE
 * =============================================================================
 *
 * Free downloadable resources for tailors - practice guides, templates, etc.
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Get current language
$currentLang = Lang::current();

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$pageTitle = 'Free Resources for Tailors | CuttingMaster';
$metaDescription = 'Download free tailoring resources - stitching practice guides, measurement templates, and pattern making tools for tailors and boutique owners.';
$metaKeywords = 'tailoring resources, stitching practice, sewing guide, free tailoring templates, blouse pattern guide';
$activePage = 'resources';
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
    .resources-section {
        padding: 6rem 2rem 4rem;
        min-height: 100vh;
        position: relative;
    }

    .resources-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .resources-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .resources-tag {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #8B7BA8;
        margin-bottom: 1rem;
    }

    .resources-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 3rem;
        font-weight: 300;
        color: #2D3748;
        margin-bottom: 1rem;
    }

    .resources-description {
        color: #718096;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .resources-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .resource-card {
        background: #FFFFFF;
        border: 1px solid rgba(177, 156, 217, 0.2);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(177, 156, 217, 0.1);
    }

    .resource-card:hover {
        border-color: rgba(177, 156, 217, 0.4);
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(177, 156, 217, 0.2);
    }

    .resource-preview {
        background: linear-gradient(135deg, #f8f4fc 0%, #fff 100%);
        padding: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        border-bottom: 1px solid rgba(177, 156, 217, 0.1);
    }

    .resource-preview img {
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
    }

    .resource-preview-icon {
        width: 80px;
        height: 80px;
        background: rgba(221, 42, 42, 0.1);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .resource-preview-icon svg {
        width: 40px;
        height: 40px;
        color: #dd2a2a;
    }

    .resource-content {
        padding: 1.5rem;
    }

    .resource-category {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #8B7BA8;
        background: rgba(139, 123, 168, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        margin-bottom: 0.75rem;
    }

    .resource-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        font-weight: 400;
        color: #2D3748;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .resource-description {
        color: #718096;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .resource-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
        font-size: 0.85rem;
        color: #A0AEC0;
    }

    .resource-meta-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .resource-download-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #dd2a2a 0%, #c41e1e 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .resource-download-btn:hover {
        background: linear-gradient(135deg, #c41e1e 0%, #a01818 100%);
        transform: translateY(-1px);
    }

    .resource-download-btn svg {
        width: 18px;
        height: 18px;
    }

    .coming-soon-badge {
        display: inline-block;
        background: #f0f0f0;
        color: #666;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    /* Featured resource */
    .resource-card.featured {
        border: 2px solid rgba(221, 42, 42, 0.3);
        position: relative;
    }

    .resource-card.featured::before {
        content: 'NEW';
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #dd2a2a;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 1px;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .resources-section {
            padding: 5rem 1rem 3rem;
        }

        .resources-title {
            font-size: 2.25rem;
        }

        .resources-grid {
            grid-template-columns: 1fr;
        }
    }
CSS;

include __DIR__ . '/../includes/header.php';
?>

    <!-- Resources Section -->
    <section class="resources-section">
        <div class="resources-container">
            <header class="resources-header">
                <span class="resources-tag">Free Downloads</span>
                <h1 class="resources-title">Tailoring Resources</h1>
                <p class="resources-description">
                    Free guides, templates, and practice materials to help you improve your tailoring skills.
                </p>
            </header>

            <div class="resources-grid">
                <!-- Stitching Practice Guide - Featured -->
                <div class="resource-card featured">
                    <div class="resource-preview">
                        <div class="resource-preview-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="resource-content">
                        <span class="resource-category">Practice Guide</span>
                        <h3 class="resource-name">Stitching Practice Guide</h3>
                        <p class="resource-description">
                            Printable worksheets to practice stitching skills. Includes straight lines, curves, corners,
                            and blouse-specific patterns like necklines, armholes, and darts. All skill levels.
                        </p>
                        <div class="resource-meta">
                            <span class="resource-meta-item">
                                <i data-lucide="file-text" style="width: 14px; height: 14px;"></i>
                                10 Pages
                            </span>
                            <span class="resource-meta-item">
                                <i data-lucide="printer" style="width: 14px; height: 14px;"></i>
                                A4 Size
                            </span>
                            <span class="resource-meta-item">
                                <i data-lucide="award" style="width: 14px; height: 14px;"></i>
                                Beginner to Advanced
                            </span>
                        </div>
                        <a href="../resources/stitching-practice-guide.html" target="_blank" class="resource-download-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            View & Download PDF
                        </a>
                    </div>
                </div>

                <!-- Measurement Template - Coming Soon -->
                <div class="resource-card">
                    <div class="resource-preview">
                        <div class="resource-preview-icon" style="background: rgba(139, 123, 168, 0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8B7BA8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="resource-content">
                        <span class="resource-category">Template</span>
                        <h3 class="resource-name">Body Measurement Chart</h3>
                        <p class="resource-description">
                            Printable measurement chart with visual guides showing where to take each measurement.
                            Perfect for recording customer measurements accurately.
                        </p>
                        <div class="resource-meta">
                            <span class="resource-meta-item">
                                <i data-lucide="file-text" style="width: 14px; height: 14px;"></i>
                                2 Pages
                            </span>
                            <span class="resource-meta-item">
                                <i data-lucide="printer" style="width: 14px; height: 14px;"></i>
                                A4 Size
                            </span>
                        </div>
                        <span class="coming-soon-badge">Coming Soon</span>
                    </div>
                </div>

                <!-- Blouse Design Sketches - Coming Soon -->
                <div class="resource-card">
                    <div class="resource-preview">
                        <div class="resource-preview-icon" style="background: rgba(139, 123, 168, 0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8B7BA8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="resource-content">
                        <span class="resource-category">Design Reference</span>
                        <h3 class="resource-name">Blouse Neckline Designs</h3>
                        <p class="resource-description">
                            Collection of 50+ popular blouse neckline designs with sketches.
                            Show customers design options and get clear requirements.
                        </p>
                        <div class="resource-meta">
                            <span class="resource-meta-item">
                                <i data-lucide="image" style="width: 14px; height: 14px;"></i>
                                50+ Designs
                            </span>
                            <span class="resource-meta-item">
                                <i data-lucide="printer" style="width: 14px; height: 14px;"></i>
                                Printable
                            </span>
                        </div>
                        <span class="coming-soon-badge">Coming Soon</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script>
        lucide.createIcons();
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
