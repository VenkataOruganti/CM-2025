<?php
/**
 * =============================================================================
 * LANGUAGE INITIALIZATION
 * =============================================================================
 *
 * Include this file at the top of any page that needs multi-language support.
 *
 * Usage:
 *   require_once __DIR__ . '/includes/lang-init.php';
 *
 * Then use translations:
 *   echo __('nav.home');
 *   echo __('messages.welcome', ['name' => $username]);
 *
 * @author CM-2025
 * @date January 2026
 */

// Load the Lang class
require_once __DIR__ . '/../lang/Lang.php';

// Initialize the language system
Lang::init();

// Make current language available globally
$GLOBALS['current_lang'] = Lang::current();
$GLOBALS['lang_direction'] = Lang::getDirection();
