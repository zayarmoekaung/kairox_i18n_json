# Release Notes - v2.0.0

## Overview
Version 2.0.0 introduces comprehensive plugin lifecycle management with proper activation, deactivation, and uninstall handlers to ensure clean installations and seamless updates.

## What's New

### � Persistent Language Selection Across Navigation
- **Automatic URL Parameter Management**: The `lang` query parameter is intelligently appended to all internal links
- **Seamless Navigation**: Language selection persists when users navigate between posts, pages, categories, and menus
- **Smart Implementation**: Only non-default languages get the parameter appended, keeping URLs clean for the default language
- **Complete Link Coverage**: Works with post links, pages, taxonomy links, navigation menus, and homepage links
- **No User Confusion**: Users see consistent translations throughout their entire browsing session

### �🎯 Installation & Activation Improvements
- **Automatic Plugin Setup**: On activation, the plugin now automatically creates the required `includes/languages` directory and initializes default configuration
- **Smart Configuration**: Default configuration is only created if it doesn't exist, preserving existing user settings during updates
- **Rewrite Rules Management**: WordPress rewrite rules are automatically flushed during activation and deactivation for proper routing

### 🧹 Clean Uninstall Process
- **New `uninstall.php` Handler**: Implements WordPress standard uninstall hook to provide graceful cleanup
- **Complete Directory Removal**: The `includes/languages` directory and all language files are completely removed during uninstallation
- **Database Cleanup**: All plugin configuration options are removed from the WordPress database
- **No Orphaned Files**: Eliminates the "Destination folder already exists" error that occurred during reinstallation

### 📝 Enhanced Documentation
- Updated README with clear installation instructions
- Added automatic setup and clean uninstall documentation
- Improved clarity on plugin lifecycle management

## Changes

### Files Added
- **`uninstall.php`** - Handles graceful plugin uninstallation with recursive directory cleanup

### Files Modified
- **`kairox_i18n_json.php`** - Added activation and deactivation hooks
- **`includes/frontend/class-i18n-runtime.php`** - Added URL language parameter persistence filters

## Technical Details

### Activation Hook (`native_i18n_activate`)
```php
- Creates `/includes/languages` directory structure
- Initializes default configuration (English as default language)
- Sets up default active languages list
- Flushes WordPress rewrite rules
```

### Deactivation Hook (`native_i18n_deactivate`)
```php
- Flushes rewrite rules for cache cleanup
- Preserves user data and configuration
- Allows for smooth reactivation
```

### Uninstall Hook (`native_i18n_uninstall`)
```php
- Removes plugin configuration from wp_options table
- Recursively deletes `/includes/languages` directory
- Cleans up all plugin-related database entries
```

### Language Parameter Persistence (`append_language_to_url`)
```php
- Filters: post_link, post_type_link, term_link, nav_menu_item_url, home_url, page_link
- Appends 'lang' query parameter to all internal links
- Skips admin URLs and external links
- Prevents duplicate parameters
- Only appends for non-default languages
```

## Bug Fixes
- ✅ Fixed "Destination folder already exists" error on plugin reinstallation
- ✅ Prevents orphaned plugin directories when uninstalling
- ✅ Ensures clean state for fresh installations
- ✅ Fixed language parameter being lost when navigating between pages
- ✅ Automatic URL parameter management eliminates user confusion with language switching

## Compatibility
- WordPress 5.0+
- PHP 7.2+
- Elementor (optional)
- Gutenberg blocks compatible

## Migration Guide
No migration required. Existing installations will:
1. Maintain all current language configurations
2. Benefit from improved activation handling on next deactivation/activation
3. Enjoy clean uninstall on future removals

## Testing Recommendations
- Test plugin activation: Verify `includes/languages` directory is created
- Test plugin deactivation: Confirm all transients and caches are cleared
- Test plugin uninstallation: Verify all directories and database entries are removed
- Test plugin reinstallation: Confirm "Destination folder already exists" error is eliminated
- Test language persistence: Switch to non-default language and navigate through posts/pages; verify `lang` parameter persists in URLs
- Test link generation: Verify all internal links include the language parameter when viewing in non-default language
- Test default language: Verify URLs stay clean (no `lang` parameter) when using default language
- Test external links: Confirm external links are not modified

## Known Limitations
None reported at this time.

## Support
For issues or questions regarding installation, activation, or uninstallation, please refer to the main README.md or open an issue on the [GitHub repository](https://github.com/zayarmoekaung/kairox_i18n_json).
