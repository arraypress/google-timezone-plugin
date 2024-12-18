# Google Maps Timezone Tester Plugin for WordPress

A WordPress plugin that provides a user interface for testing and demonstrating the [Google Maps Timezone Library](https://github.com/arraypress/google-timezone) integration. This plugin allows you to easily test timezone detection for any coordinates and manage API settings through the WordPress admin interface.

## Features

- Visual interface for timezone detection testing
- Real-time timezone information display
- Configurable caching options
- Multi-language support for timezone names
- Comprehensive timezone details including:
    - Timezone ID and localized name
    - Raw UTC and DST offsets
    - Local date/time calculations
    - DST status indicators

## Requirements

- PHP 7.4 or later
- WordPress 5.0 or later
- Google Maps API key with Timezone API enabled

## Installation

1. Download or clone this repository
2. Place in your WordPress plugins directory
3. Run `composer install` in the plugin directory
4. Activate the plugin in WordPress
5. Add your Google Maps API key in Tools > Timezone Detection

## Usage

1. Navigate to Tools > Timezone Detection in your WordPress admin panel
2. Enter your Google Maps API key in the settings section
3. Configure caching preferences if desired
4. Use the timezone detection form to test coordinates
5. View comprehensive timezone information in the results table

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/google-timezone)
- [Issue Tracker](https://github.com/arraypress/google-timezone/issues)