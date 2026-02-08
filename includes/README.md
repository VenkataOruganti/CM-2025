# Header and Footer Include System

This directory contains reusable header and footer files for the CuttingMaster website.

## Files

- **header.php** - Page header with navigation
- **footer.php** - Page footer with scripts

## How to Use

### Basic Usage

At the top of your PHP page, set configuration variables, then include the header:

```php
<?php
// Your PHP logic here...

// Configure header
$pageTitle = 'Your Page Title';
$cssPath = '../css/styles.css';  // Adjust path as needed
$logoPath = '../images/logo.png';  // Adjust path as needed
$logoLink = '../index.php';  // Where logo should link
$navBase = '';  // Empty for pages/ directory, or '' for root

// Include header
include __DIR__ . '/../includes/header.php';
?>

<!-- Your page content here -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

## Configuration Variables

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `$pageTitle` | Page title (appears in browser tab) | `'Register'` |
| `$cssPath` | Path to CSS file | `'../css/styles.css'` |
| `$logoPath` | Path to logo image | `'../images/logo.png'` |
| `$logoLink` | Where logo should link | `'../index.php'` |
| `$navBase` | Base path for navigation links | `''` for pages/ dir |

### Optional Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `$additionalStyles` | Page-specific CSS | See register.php |
| `$additionalScripts` | Page-specific JavaScript | See register.php |
| `$activePage` | Highlight active nav item | `'pattern-studio'` |
| `$isLoggedIn` | Show logged-in navigation | `true` or `false` |
| `$currentUser` | User data for dropdown | `getCurrentUser()` |
| `$userType` | User type for dashboard link | `'boutique'` |
| `$showRegister` | Show REGISTER button | `true` or `false` |
| `$showLoginRegister` | Show LOGIN / REGISTER | `true` or `false` |

## Examples

### Example 1: Simple Page (pages/ directory)

```php
<?php
session_start();

// Set header configuration
$pageTitle = 'Contact Us';
$cssPath = '../css/styles.css';
$logoPath = '../images/logo.png';
$logoLink = '../index.php';
$navBase = '';
$activePage = 'contact-us';

include __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <h1>Contact Us</h1>
    <!-- Your content -->
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### Example 2: Page with Custom Styles (like register.php)

```php
<?php
session_start();

// Set header configuration
$pageTitle = 'Register';
$cssPath = '../css/styles.css';
$logoPath = '../images/logo.png';
$logoLink = '../index.php';
$navBase = '';

// Add custom CSS
$additionalStyles = '
.custom-class {
    color: red;
}
';

// Add custom JavaScript
$additionalScripts = "
console.log('Custom script');
";

include __DIR__ . '/../includes/header.php';
?>

<!-- Your content -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### Example 3: Root Directory Page (index.php)

```php
<?php
session_start();

// Set header configuration
$pageTitle = 'Home';
$cssPath = 'css/styles.css';  // No ../ prefix
$logoPath = 'images/logo.png';  // No ../ prefix
$logoLink = 'index.php';
$navBase = '';  // Links go to pages/

include __DIR__ . '/includes/header.php';
?>

<!-- Your content -->

<?php include __DIR__ . '/includes/footer.php'; ?>
```

### Example 4: Logged-in User Page

```php
<?php
session_start();
require_once __DIR__ . '/../config/auth.php';

$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();
$userType = $currentUser['user_type'] ?? 'individual';

$pageTitle = 'Dashboard';
$cssPath = '../css/styles.css';
$logoPath = '../images/logo.png';
$logoLink = '../index.php';
$navBase = '';

include __DIR__ . '/../includes/header.php';
?>

<!-- Your dashboard content -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

## Active Page Highlighting

To highlight the active navigation item, set `$activePage`:

```php
$activePage = 'pattern-studio';  // Highlights PATTERN STUDIO link
$activePage = 'wholesale-catalog';  // Highlights WHOLESALE MARKETPLACE link
$activePage = 'contact-us';  // Highlights CONTACT US link
```

## Benefits

1. **Single Source of Truth** - Update logo/navigation once, reflects everywhere
2. **Cleaner Code** - Pages focus on their content, not boilerplate
3. **Easier Maintenance** - Fix bugs or add features in one place
4. **Consistency** - All pages automatically stay in sync
5. **Smaller File Sizes** - Less duplicated code

## Migration Checklist

When converting an existing page to use header/footer includes:

1. ✅ Keep PHP logic at the top
2. ✅ Set configuration variables before including header
3. ✅ Replace `<!DOCTYPE html>` through `</nav>` with header include
4. ✅ Move page-specific `<style>` to `$additionalStyles` variable
5. ✅ Replace `<footer>` through `</html>` with footer include
6. ✅ Move page-specific `<script>` to `$additionalScripts` variable
7. ✅ Test the page to ensure it works correctly

## Notes

- Always use `__DIR__` for include paths to ensure correct relative paths
- The header/footer system is flexible - add new variables as needed
- If a variable isn't set, the header/footer uses sensible defaults
