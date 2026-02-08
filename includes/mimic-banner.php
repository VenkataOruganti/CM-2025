<?php
/**
 * Mimic Banner - Shows when admin is impersonating a user
 * Include this file at the top of user dashboards
 */
if (isset($_SESSION['is_mimicking']) && $_SESSION['is_mimicking'] === true): ?>
<div class="mimic-banner">
    <div class="mimic-banner-content">
        <span class="mimic-banner-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </span>
        <span class="mimic-banner-text">
            You are viewing as <strong><?php echo htmlspecialchars($_SESSION['mimicked_username']); ?></strong>
        </span>
        <a href="exit-mimic.php" class="mimic-banner-exit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Exit & Return to Admin
        </a>
    </div>
</div>
<style>
    .mimic-banner {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
        padding: 0.75rem 1rem;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    }

    .mimic-banner-content {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .mimic-banner-icon {
        display: flex;
        align-items: center;
    }

    .mimic-banner-text {
        font-size: 0.9375rem;
    }

    .mimic-banner-text strong {
        font-weight: 600;
    }

    .mimic-banner-exit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.2s;
    }

    .mimic-banner-exit:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Push body content down when mimic banner is shown */
    body.has-mimic-banner {
        padding-top: 52px;
    }

    body.has-mimic-banner #navbar {
        top: 52px;
    }
</style>
<script>
    document.body.classList.add('has-mimic-banner');
</script>
<?php endif; ?>
