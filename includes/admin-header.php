<!-- Admin Header Styles -->
<style>
    .admin-navbar {
        background: white;
        border-bottom: 1px solid #E2E8F0;
        padding: 0.75rem 0;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .admin-nav-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-nav-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .admin-nav-logo img {
        height: 32px;
        width: auto;
    }

    .admin-nav-links {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .admin-nav-link {
        color: #4A5568;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .admin-nav-link:hover {
        color: #2D3748;
        background: #F7FAFC;
    }

    .admin-nav-link.active {
        color: #3B82F6;
        background: #EBF5FF;
    }

    .admin-nav-dropdown {
        position: relative;
    }

    .admin-nav-dropdown-toggle {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .admin-nav-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        min-width: 180px;
        padding: 0.5rem 0;
        z-index: 1001;
        padding-top: 0.75rem;
    }

    /* Invisible bridge to prevent dropdown from closing when moving mouse */
    .admin-nav-dropdown-menu::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 0;
        right: 0;
        height: 10px;
        background: transparent;
    }

    .admin-nav-dropdown:hover .admin-nav-dropdown-menu {
        display: block;
    }

    .admin-nav-dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
    }

    .admin-nav-dropdown-label {
        color: #718096;
        display: block;
    }

    .admin-nav-dropdown-value {
        color: #2D3748;
        font-weight: 500;
    }

    .admin-logout-btn {
        color: #DC2626;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.2s;
        margin-left: 0.25rem;
    }

    .admin-logout-btn:hover {
        background: #FEE2E2;
    }
</style>

<!-- Admin Navigation -->
<nav class="admin-navbar">
    <div class="admin-nav-container">
        <div class="admin-nav-logo" onclick="window.location.href='../index.php'">
            <img src="../images/cm-logo.svg" alt="CuttingMaster">
        </div>
        <div class="admin-nav-links">
            <a href="dashboard-admin.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard-admin.php' ? 'active' : ''; ?>">Dashboard</a>
            <div class="admin-nav-dropdown">
                <a href="#" class="admin-nav-link admin-nav-dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin-pattern-portfolio.php', 'admin-predesigned-patterns.php']) ? 'active' : ''; ?>">
                    Pattern Catalog
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <div class="admin-nav-dropdown-menu" style="min-width: 180px;">
                    <a href="admin-pattern-portfolio.php" class="admin-nav-link" style="display: block; padding: 0.5rem 1rem;">Patterns & Designs</a>
                    <a href="admin-predesigned-patterns.php" class="admin-nav-link" style="display: block; padding: 0.5rem 1rem;">Pre-designed Patterns</a>
                </div>
            </div>
            <a href="admin-tailoring-portfolio.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin-tailoring-portfolio.php' ? 'active' : ''; ?>">Tailoring Portfolio</a>
            <a href="admin-wholesale-portfolio.php" class="admin-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin-wholesale-portfolio.php' || basename($_SERVER['PHP_SELF']) === 'admin-wholesale-variants.php') ? 'active' : ''; ?>">Wholesale Portfolio</a>
            <a href="admin-enquiries.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin-enquiries.php' ? 'active' : ''; ?>">Enquiries</a>
            <div class="admin-nav-dropdown">
                <a href="#" class="admin-nav-link admin-nav-dropdown-toggle">
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <div class="admin-nav-dropdown-menu">
                    <div class="admin-nav-dropdown-item">
                        <span class="admin-nav-dropdown-label">Username:</span>
                        <span class="admin-nav-dropdown-value"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    </div>
                    <div class="admin-nav-dropdown-item">
                        <span class="admin-nav-dropdown-label">Account Type:</span>
                        <span class="admin-nav-dropdown-value">Administrator</span>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="admin-logout-btn">Logout</a>
        </div>
    </div>
</nav>
