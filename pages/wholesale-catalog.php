<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Fetch wholesale products from database with their variants
$wholesaleProducts = [];
$availableCategories = [];
$availableColors = [];
$availableSizes = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM wholesale_portfolio WHERE status = 'active' ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $wholesaleProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch variants for each product
    foreach ($wholesaleProducts as &$product) {
        // Collect unique categories
        if (!empty($product['category'])) {
            $availableCategories[] = $product['category'];
        }

        $variantStmt = $pdo->prepare("SELECT color, size, price FROM wholesale_variants WHERE product_id = ? AND status = 'active'");
        $variantStmt->execute([$product['id']]);
        $variants = $variantStmt->fetchAll(PDO::FETCH_ASSOC);

        // Collect unique colors and sizes
        $colors = [];
        $sizes = [];
        foreach ($variants as $variant) {
            if (!empty($variant['color'])) {
                $colorLower = strtolower(trim($variant['color']));
                $colors[] = $colorLower;
                $availableColors[] = $colorLower;
            }
            if (!empty($variant['size'])) {
                $sizeUpper = strtoupper(trim($variant['size']));
                $sizes[] = $sizeUpper;
                $availableSizes[] = $sizeUpper;
            }
        }
        $product['colors'] = array_unique($colors);
        $product['sizes'] = array_unique($sizes);
    }
    unset($product); // Break reference

    // Get unique values
    $availableCategories = array_unique($availableCategories);
    $availableColors = array_unique($availableColors);
    $availableSizes = array_unique($availableSizes);

    // Sort sizes in logical order
    $sizeOrder = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6, '2XL' => 6, '3XL' => 7, '4XL' => 8];
    usort($availableSizes, function($a, $b) use ($sizeOrder) {
        $orderA = $sizeOrder[$a] ?? 99;
        $orderB = $sizeOrder[$b] ?? 99;
        return $orderA - $orderB;
    });

} catch(PDOException $e) {
    // Table might not exist yet, silently fail
    $wholesaleProducts = [];
}

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$pageTitle = 'Wholesale Fashion Catalog - Bulk Garments & Clothing';
$metaDescription = 'Browse CuttingMaster wholesale catalog for bulk garments, ethnic wear, saree blouses, and fashion clothing. Quality wholesale products for boutiques, retailers, and fashion houses across India. Competitive pricing and MOQ options.';
$metaKeywords = 'wholesale clothing India, bulk garments, wholesale saree blouses, ethnic wear wholesale, fashion wholesale, boutique suppliers, wholesale fashion catalog, bulk clothing order';
$activePage = 'wholesale-catalog';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = '../index.php';
$navBase = '../';

// Get current user info for header
if ($isLoggedIn) {
    require_once __DIR__ . '/../config/auth.php';
    $currentUser = getCurrentUser();
}

// Include shared header
include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero catalog-hero">
        <div class="hero-container catalog-hero-container">
            <div class="hero-content catalog-hero-content">
                <p class="hero-tag">Wholesale Collection</p>
                <h1 class="hero-title">
                    <span class="hero-title-accent">Premium</span> Wholesale Catalog
                </h1>
                <p class="hero-description catalog-hero-description">
                    Discover our extensive collection of wholesale garments.<br>
                    Quality craftsmanship meets scalable manufacturing for boutiques, retailers, and fashion houses.
                </p>
            </div>
        </div>
    </section>

    <!-- Catalog Section with Sidebar -->
    <section class="catalog-section">
        <div class="catalog-container">

            <!-- Left Sidebar Filters -->
            <aside class="filter-sidebar">
                <div class="filter-panel">
                    <h3 class="filter-title">Filters</h3>

                    <!-- Category Filter -->
                    <?php if (!empty($availableCategories)): ?>
                    <div class="filter-group">
                        <h4 class="filter-heading">CATEGORY</h4>
                        <div class="filter-grid-<?php echo count($availableCategories) <= 2 ? '2col' : '2col'; ?>">
                            <label class="filter-label">
                                <input type="radio" name="category" value="all" checked class="filter-radio">
                                <span>All Products</span>
                            </label>
                            <?php foreach ($availableCategories as $category): ?>
                            <label class="filter-label">
                                <input type="radio" name="category" value="<?php echo htmlspecialchars($category); ?>" class="filter-radio">
                                <span><?php echo htmlspecialchars($category); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Price Range Filter -->
                    <div class="filter-group-bordered">
                        <h4 class="filter-heading">PRICE RANGE</h4>
                        <div class="filter-grid-1col">
                            <label class="filter-label">
                                <input type="checkbox" value="0-10000" class="filter-checkbox price-filter">
                                <span>Under ₹10K</span>
                            </label>
                            <label class="filter-label">
                                <input type="checkbox" value="10000-25000" class="filter-checkbox price-filter">
                                <span>₹10K - ₹25K</span>
                            </label>
                            <label class="filter-label">
                                <input type="checkbox" value="25000-50000" class="filter-checkbox price-filter">
                                <span>₹25K - ₹50K</span>
                            </label>
                            <label class="filter-label">
                                <input type="checkbox" value="50000+" class="filter-checkbox price-filter">
                                <span>Above ₹50K</span>
                            </label>
                        </div>
                    </div>

                    <!-- Color Filter -->
                    <?php if (!empty($availableColors)): ?>
                    <div class="filter-group-bordered">
                        <h4 class="filter-heading">COLOR</h4>
                        <div class="color-filter-container">
                            <?php foreach ($availableColors as $color): ?>
                            <button class="color-filter color-<?php echo htmlspecialchars($color); ?>" data-color="<?php echo htmlspecialchars($color); ?>" title="<?php echo ucfirst(htmlspecialchars($color)); ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Size Filter -->
                    <?php if (!empty($availableSizes)): ?>
                    <div class="filter-group-bordered">
                        <h4 class="filter-heading">SIZE</h4>
                        <div class="filter-grid-3col">
                            <?php foreach ($availableSizes as $size): ?>
                            <label class="filter-label">
                                <input type="checkbox" value="<?php echo htmlspecialchars($size); ?>" class="filter-checkbox size-filter">
                                <span><?php echo htmlspecialchars($size); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Clear Filters Button -->
                    <button id="clear-filters" class="clear-filters-btn">
                        CLEAR ALL FILTERS
                    </button>
                </div>
            </aside>

            <!-- Right Side: Product Grid -->
            <div>
                <div class="catalog-controls">
                    <p id="product-count" class="product-count">Showing 12 products</p>
                    <select id="sort-select" class="sort-select">
                        <option value="default">Sort by: Default</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name">Name: A to Z</option>
                    </select>
                </div>
                <div id="wholesale-grid" class="ecommerce-grid">
                    <!-- Products will be dynamically inserted here -->
                </div>

                <!-- Pagination -->
                <div id="pagination" class="pagination">
                    <!-- Pagination buttons will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
<?php include __DIR__ . "/../includes/footer.php"; ?>

    <!-- Initialize Lucide Icons and Products -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        window.addEventListener('load', function() {
            try {
                console.log('Window loaded');

                function initializePage() {
                    if (typeof lucide === 'undefined' || typeof lucide.createIcons !== 'function') {
                        console.log('Waiting for Lucide to load...');
                        setTimeout(initializePage, 100);
                        return;
                    }

                    console.log('Lucide available, initializing...');

                    // Wholesale product data from database
                    const wholesaleProducts = <?php echo json_encode(array_map(function($item) {
                        // Extract numeric price from string (e.g., "₹28,000" -> 28000)
                        $numericPrice = 0;
                        if (!empty($item['price'])) {
                            $priceStr = preg_replace('/[^0-9.]/', '', str_replace(',', '', $item['price']));
                            $numericPrice = floatval($priceStr);
                        }
                        return [
                            'id' => $item['id'],
                            'icon' => 'package',
                            'category' => $item['category'] ?? 'Wholesale',
                            'name' => $item['title'],
                            'description' => $item['description'] ?? '',
                            'price' => $item['price'] ?? '',
                            'numericPrice' => $numericPrice,
                            'image' => $item['image'] ? '../' . $item['image'] : null,
                            'colors' => array_values($item['colors'] ?? []),
                            'sizes' => array_values($item['sizes'] ?? [])
                        ];
                    }, $wholesaleProducts)); ?>;

                    // Filter state
                    let filters = {
                        category: 'all',
                        priceRanges: [],
                        colors: [],
                        sizes: []
                    };

                    // Sort state
                    let currentSort = 'default';

                    // Pagination state
                    let currentPage = 1;
                    const itemsPerPage = 6;

                    // Function to render wholesale products
                    function renderProducts() {
                        const grid = document.getElementById('wholesale-grid');
                        grid.innerHTML = '';

                        let filteredProducts = wholesaleProducts.filter(product => {
                            // Category filter
                            if (filters.category !== 'all' && product.category !== filters.category) {
                                return false;
                            }

                            // Price range filter
                            if (filters.priceRanges.length > 0) {
                                const price = product.numericPrice;
                                let priceMatch = false;
                                for (const range of filters.priceRanges) {
                                    if (range === '0-10000' && price >= 0 && price <= 10000) priceMatch = true;
                                    else if (range === '10000-25000' && price > 10000 && price <= 25000) priceMatch = true;
                                    else if (range === '25000-50000' && price > 25000 && price <= 50000) priceMatch = true;
                                    else if (range === '50000+' && price > 50000) priceMatch = true;
                                }
                                if (!priceMatch) return false;
                            }

                            // Color filter - check if product has any of the selected colors
                            if (filters.colors.length > 0) {
                                const productColors = product.colors || [];
                                const hasMatchingColor = filters.colors.some(filterColor =>
                                    productColors.some(productColor =>
                                        productColor.toLowerCase().includes(filterColor.toLowerCase()) ||
                                        filterColor.toLowerCase().includes(productColor.toLowerCase())
                                    )
                                );
                                if (!hasMatchingColor) return false;
                            }

                            // Size filter - check if product has any of the selected sizes
                            if (filters.sizes.length > 0) {
                                const productSizes = product.sizes || [];
                                const hasMatchingSize = filters.sizes.some(filterSize =>
                                    productSizes.some(productSize =>
                                        productSize.toUpperCase() === filterSize.toUpperCase()
                                    )
                                );
                                if (!hasMatchingSize) return false;
                            }

                            return true;
                        });

                        // Apply sorting
                        if (currentSort === 'price-low') {
                            filteredProducts.sort((a, b) => a.numericPrice - b.numericPrice);
                        } else if (currentSort === 'price-high') {
                            filteredProducts.sort((a, b) => b.numericPrice - a.numericPrice);
                        } else if (currentSort === 'name') {
                            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
                        }

                        // Calculate total pages
                        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);

                        // Ensure current page is valid
                        if (currentPage > totalPages && totalPages > 0) {
                            currentPage = totalPages;
                        }
                        if (currentPage < 1) {
                            currentPage = 1;
                        }

                        // Get products for current page
                        const startIndex = (currentPage - 1) * itemsPerPage;
                        const endIndex = startIndex + itemsPerPage;
                        const paginatedProducts = filteredProducts.slice(startIndex, endIndex);

                        // Show no results message if no products match filters
                        if (filteredProducts.length === 0) {
                            const noResultsDiv = document.createElement('div');
                            noResultsDiv.className = 'no-results-message';
                            noResultsDiv.style.cssText = 'grid-column: 1 / -1; display: flex; justify-content: center; align-items: center; min-height: 300px;';
                            noResultsDiv.innerHTML = `
                                <div style="text-align: center; padding: 3rem 2rem;">
                                    <i data-lucide="search-x" style="width: 48px; height: 48px; color: rgba(177, 156, 217, 0.5); margin-bottom: 1rem; display: block; margin-left: auto; margin-right: auto;"></i>
                                    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #2D3748; margin-bottom: 0.5rem;">No Products Found</h3>
                                    <p style="color: rgba(45, 55, 72, 0.7); font-size: 0.95rem; margin-bottom: 1rem;">No products match your current filter selection.</p>
                                    <p style="color: rgba(45, 55, 72, 0.6); font-size: 0.875rem;">Try adjusting or clearing your filters to see available designs.</p>
                                </div>
                            `;
                            grid.appendChild(noResultsDiv);
                        } else {
                            paginatedProducts.forEach(product => {
                                const card = document.createElement('div');
                                card.className = 'ecommerce-card';
                                card.style.cursor = 'pointer';

                                // Check if product has an image from database
                                const imageContent = product.image
                                    ? `<img src="${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover; object-position: top;">`
                                    : `<div class="ecommerce-placeholder">
                                            <i data-lucide="${product.icon}" class="ecommerce-icon product-icon"></i>
                                            <p class="ecommerce-placeholder-label">Product Image</p>
                                        </div>`;

                                card.innerHTML = `
                                    <div class="ecommerce-image">
                                        ${imageContent}
                                    </div>
                                    <div class="ecommerce-info">
                                        <p class="ecommerce-category">${product.category}</p>
                                        <h3 class="ecommerce-name">${product.name}</h3>
                                        <p class="ecommerce-description">${product.description}</p>
                                        <p class="ecommerce-price">${product.price}</p>
                                    </div>
                                `;

                                // Make card clickable to open product details
                                card.addEventListener('click', function() {
                                    window.location.href = 'wholesale-product.php?id=' + product.id;
                                });

                                grid.appendChild(card);
                            });
                        }

                        // Update product count
                        const showingStart = filteredProducts.length === 0 ? 0 : startIndex + 1;
                        const showingEnd = Math.min(endIndex, filteredProducts.length);
                        document.getElementById('product-count').textContent = `Showing ${showingStart}-${showingEnd} of ${filteredProducts.length} products`;

                        // Render pagination
                        renderPagination(totalPages);

                        // Re-initialize Lucide icons
                        lucide.createIcons();
                    }

                    // Function to render pagination controls
                    function renderPagination(totalPages) {
                        const paginationContainer = document.getElementById('pagination');
                        paginationContainer.innerHTML = '';

                        if (totalPages <= 1) {
                            return; // No pagination needed
                        }

                        // Previous button
                        const prevButton = document.createElement('button');
                        prevButton.innerHTML = '&laquo; Previous';
                        prevButton.disabled = currentPage === 1;
                        prevButton.style.cssText = `
                            padding: 0.5rem 1rem;
                            border: 1px solid ${currentPage === 1 ? '#ddd' : '#2D3748'};
                            background: ${currentPage === 1 ? '#f5f5f5' : 'white'};
                            color: ${currentPage === 1 ? '#999' : '#2D3748'};
                            border-radius: 6px;
                            cursor: ${currentPage === 1 ? 'not-allowed' : 'pointer'};
                            font-size: 0.875rem;
                            font-weight: 500;
                            transition: all 0.3s;
                        `;
                        if (currentPage > 1) {
                            prevButton.addEventListener('click', () => {
                                currentPage--;
                                renderProducts();
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            });
                            prevButton.addEventListener('mouseenter', () => {
                                prevButton.style.background = '#2D3748';
                                prevButton.style.color = 'white';
                            });
                            prevButton.addEventListener('mouseleave', () => {
                                prevButton.style.background = 'white';
                                prevButton.style.color = '#2D3748';
                            });
                        }
                        paginationContainer.appendChild(prevButton);

                        // Page numbers
                        const maxPageButtons = 5;
                        let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
                        let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

                        if (endPage - startPage < maxPageButtons - 1) {
                            startPage = Math.max(1, endPage - maxPageButtons + 1);
                        }

                        for (let i = startPage; i <= endPage; i++) {
                            const pageButton = document.createElement('button');
                            pageButton.textContent = i;
                            const isActive = i === currentPage;
                            pageButton.style.cssText = `
                                padding: 0.5rem 0.75rem;
                                border: 1px solid ${isActive ? '#D946A6' : '#2D3748'};
                                background: ${isActive ? '#D946A6' : 'white'};
                                color: ${isActive ? 'white' : '#2D3748'};
                                border-radius: 6px;
                                cursor: pointer;
                                font-size: 0.875rem;
                                font-weight: ${isActive ? 'bold' : '500'};
                                text-decoration: ${isActive ? 'underline' : 'none'};
                                min-width: 40px;
                                transition: all 0.3s;
                            `;
                            if (!isActive) {
                                pageButton.addEventListener('click', () => {
                                    currentPage = i;
                                    renderProducts();
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                });
                                pageButton.addEventListener('mouseenter', () => {
                                    pageButton.style.background = '#2D3748';
                                    pageButton.style.color = 'white';
                                });
                                pageButton.addEventListener('mouseleave', () => {
                                    pageButton.style.background = 'white';
                                    pageButton.style.color = '#2D3748';
                                });
                            }
                            paginationContainer.appendChild(pageButton);
                        }

                        // Next button
                        const nextButton = document.createElement('button');
                        nextButton.innerHTML = 'Next &raquo;';
                        nextButton.disabled = currentPage === totalPages;
                        nextButton.style.cssText = `
                            padding: 0.5rem 1rem;
                            border: 1px solid ${currentPage === totalPages ? '#ddd' : '#2D3748'};
                            background: ${currentPage === totalPages ? '#f5f5f5' : 'white'};
                            color: ${currentPage === totalPages ? '#999' : '#2D3748'};
                            border-radius: 6px;
                            cursor: ${currentPage === totalPages ? 'not-allowed' : 'pointer'};
                            font-size: 0.875rem;
                            font-weight: 500;
                            transition: all 0.3s;
                        `;
                        if (currentPage < totalPages) {
                            nextButton.addEventListener('click', () => {
                                currentPage++;
                                renderProducts();
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            });
                            nextButton.addEventListener('mouseenter', () => {
                                nextButton.style.background = '#2D3748';
                                nextButton.style.color = 'white';
                            });
                            nextButton.addEventListener('mouseleave', () => {
                                nextButton.style.background = 'white';
                                nextButton.style.color = '#2D3748';
                            });
                        }
                        paginationContainer.appendChild(nextButton);
                    }

                    // Initialize Lucide icons
                    lucide.createIcons();

                    // Load default products
                    renderProducts();

                    // Category filter listeners
                    const categoryRadios = document.querySelectorAll('.filter-radio');
                    categoryRadios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            filters.category = this.value;
                            currentPage = 1; // Reset to first page
                            renderProducts();
                        });
                    });

                    // Color filter listeners
                    const colorButtons = document.querySelectorAll('.color-filter');
                    colorButtons.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const color = this.getAttribute('data-color');
                            const isSelected = this.style.borderColor === 'rgb(177, 156, 217)';

                            if (isSelected) {
                                this.style.borderColor = '#ddd';
                                this.style.borderWidth = '2px';
                                filters.colors = filters.colors.filter(c => c !== color);
                            } else {
                                this.style.borderColor = '#B19CD9';
                                this.style.borderWidth = '3px';
                                filters.colors.push(color);
                            }
                            currentPage = 1; // Reset to first page
                            renderProducts();
                        });
                    });

                    // Price filter listeners
                    const priceFilters = document.querySelectorAll('.price-filter');
                    priceFilters.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                filters.priceRanges.push(this.value);
                            } else {
                                filters.priceRanges = filters.priceRanges.filter(p => p !== this.value);
                            }
                            currentPage = 1; // Reset to first page
                            renderProducts();
                        });
                    });

                    // Size filter listeners
                    const sizeFilters = document.querySelectorAll('.size-filter');
                    sizeFilters.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                filters.sizes.push(this.value);
                            } else {
                                filters.sizes = filters.sizes.filter(s => s !== this.value);
                            }
                            currentPage = 1; // Reset to first page
                            renderProducts();
                        });
                    });

                    // Sort select listener
                    const sortSelect = document.getElementById('sort-select');
                    sortSelect.addEventListener('change', function() {
                        currentSort = this.value;
                        currentPage = 1; // Reset to first page
                        renderProducts();
                    });

                    // Clear filters button
                    document.getElementById('clear-filters').addEventListener('click', function() {
                        // Reset filters
                        filters = {
                            category: 'all',
                            priceRanges: [],
                            colors: [],
                            sizes: []
                        };

                        // Reset sort
                        currentSort = 'default';

                        // Reset pagination
                        currentPage = 1;

                        // Reset UI
                        document.querySelector('input[value="all"]').checked = true;
                        document.querySelectorAll('.filter-checkbox').forEach(cb => cb.checked = false);
                        document.querySelectorAll('.color-filter').forEach(btn => {
                            btn.style.borderColor = '#ddd';
                            btn.style.borderWidth = '2px';
                        });
                        document.getElementById('sort-select').value = 'default';

                        renderProducts();
                    });

                    // Navbar scroll effect
                    window.addEventListener('scroll', function() {
                        const navbar = document.getElementById('navbar');
                        if (window.scrollY > 50) {
                            navbar.classList.add('scrolled');
                        } else {
                            navbar.classList.remove('scrolled');
                        }
                    });
                }

                // Start initialization
                initializePage();

            } catch(error) {
                console.error('Fatal error in initialization:', error);
            }
        });
    </script>
</body>
</html>
