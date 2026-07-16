# kairox_i18n_json

A lightweight WordPress localization plugin that uses JSON files for language storage and renders a hover dropdown language switcher with flags.

## Features

- Stores translations in JSON files under `languages/`
- Supports translated content via shortcode and runtime language switching
- Provides a hover dropdown language switcher with emoji flags
- Loads flag metadata from `includes/frontend/flags.json`
- Supports Gutenberg block, Elementor widget, and classic widget insertion

## Installation

1. Copy the plugin folder to `wp-content/plugins/kairox_i18n_json`
2. Activate the plugin from the WordPress admin dashboard
3. Configure languages in the plugin settings

## Usage

### Shortcode

- Render a translation value: `[i18n key="hello.world"]`
- Render the language switcher: `[lang_switcher]`

### Block

- Add the native language switcher block from the Gutenberg editor

### Elementor

- Use the `Language Switcher` widget when Elementor is installed

### Widget

- Add the classic `Language Switcher` widget to any sidebar or footer area

## Language Flags

Flag metadata is stored in `includes/frontend/flags.json`. Add or update languages there to extend the displayed dropdown list.

## Configuration

Translation files are stored in the `languages/` folder. Each file should be named after a language code, for example:

- `languages/en.json`
- `languages/es.json`

Each JSON file should be a simple key/value object, or nested objects for scoped translation keys. Example:

```json
{
  "hello": {
    "world": "Hello, world!"
  },
  "button": "Click me"
}
```

The shortcode `[i18n key="hello.world"]` will output the nested value `Hello, world!`.

## Notes

- The current language is stored in a cookie and switched using the `lang` query parameter
- Custom translation labels are sourced from plugin configuration when available
