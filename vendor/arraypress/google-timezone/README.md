# Google Maps Timezone API for WordPress

A PHP library for integrating with the Google Maps Timezone API in WordPress. This library provides robust timezone detection and information retrieval with support for WordPress transient caching and WP_Error handling.

## Features

- ✅ **Timezone Detection**: Get timezone information for any coordinates
- 🌍 **Global Coverage**: Support for all timezones worldwide
- ⏰ **Time Calculations**: Built-in time offset and DST handling
- 🔄 **Response Parsing**: Clean response object for easy data access
- ⚡ **WordPress Integration**: Native transient caching and WP_Error support
- 🛡️ **Type Safety**: Full type hinting and strict types
- 📅 **DST Detection**: Automatic daylight saving time detection
- ⌚ **Time Formatting**: Multiple time format options
- 🌐 **Language Support**: Localized timezone names
- 🔍 **Detailed Information**: Access to comprehensive timezone data

## Requirements

- PHP 7.4 or later
- WordPress 5.0 or later
- Google Maps API key with Timezone API enabled

## Installation

Install via Composer:

```bash
composer require arraypress/google-timezone
```

## Basic Usage

```php
use ArrayPress\Google\Timezone\Client;

// Initialize client with your API key
$client = new Client( 'your-google-api-key' );

// Get timezone for coordinates
$result = $client->get_timezone( 37.4224764, -122.0842499 );
if ( ! is_wp_error( $result ) ) {
    // Get timezone information
    echo "Timezone: {$result->get_timezone_id() }\n";
    echo "Local Time: {$result->get_datetime()->format( 'Y-m-d H:i:s') }\n";
    echo "UTC Offset: {$result->get_formatted_offset() }\n";
}
```

## Extended Examples

### Working with Different Time Formats

```php
// Get timezone with specific timestamp
$timestamp = strtotime( '2024-12-25 12:00:00');
$result = $client->get_timezone(
    37.4224764,
    -122.0842499,
    $timestamp
);

if ( ! is_wp_error( $result ) ) {
    $datetime = $result->get_datetime( $timestamp );
    echo "Date: {$datetime->format( 'Y-m-d') }\n";
    echo "Time: {$datetime->format( 'H:i:s') }\n";
    echo "Timezone Name: {$result->get_timezone_name() }\n";
    echo "Abbreviated: {$result->get_abbreviated_name() }\n";
}
```

### DST and Offset Handling

```php
$result = $client->get_timezone( 37.4224764, -122.0842499 );
if (!is_wp_error( $result)) {
    // Check DST status
    if ( $result->is_dst()) {
        echo "Location is currently observing DST\n";
        echo "DST Offset: {$result->get_dst_offset() } seconds\n";
    }
    
    // Get various offsets
    echo "Raw UTC Offset: {$result->get_raw_offset() } seconds\n";
    echo "Total Offset: {$result->get_total_offset() } seconds\n";
    echo "Formatted Offset: {$result->get_formatted_offset() }\n";
}
```

### Working with Localized Names

```php
// Get timezone info in French
$result = $client->get_timezone(
    48.8566,  // Paris coordinates
    2.3522,
    null,     // Current time
    'fr'      // French language
);

if ( ! is_wp_error( $result ) ) {
    echo "Nom du fuseau horaire: {$result->get_timezone_name() }\n";
}
```

### Handling Responses with Caching

```php
// Initialize with custom cache duration (1 hour = 3600 seconds)
$client = new Client( 'your-api-key', true, 3600);

// Results will be cached
$result = $client->get_timezone( 37.4224764, -122.0842499 );

// Clear specific cache
$client->clear_cache( 'timezone_37.4224764_-122.0842499' );

// Clear all timezone caches
$client->clear_cache();
```

## API Methods

### Client Methods

* `get_timezone( $latitude, $longitude, $timestamp = null, $language = null)`: Get timezone information
* `clear_cache( $identifier = null)`: Clear cached responses

### Response Methods

#### Timezone Information
* `get_timezone_id()`: Get the IANA timezone identifier
* `get_timezone_name()`: Get the localized timezone name
* `get_abbreviated_name()`: Get abbreviated timezone name
* `is_valid()`: Check if timezone data is valid
* `is_dst()`: Check if location is observing DST

#### Offset Methods
* `get_raw_offset()`: Get base UTC offset in seconds
* `get_dst_offset()`: Get DST offset in seconds
* `get_total_offset()`: Get combined UTC and DST offset
* `get_formatted_offset()`: Get human-readable offset string

#### Time Operations
* `get_datetime()`: Get DateTime object for timezone
* `to_array()`: Get all timezone data as array

#### Basic Example
```php
use ArrayPress\Google\Timezone\Client;

// Initialize client
$client = new Client( 'your-google-api-key');

// Get timezone for coordinates
$result = $client->get_timezone(37.4224764, -122.0842499);
if ( ! is_wp_error( $result ) ) {
    if ( $result->is_valid() ) {
        // Get basic information
        echo "Timezone ID: {$result->get_timezone_id() }\n";
        echo "Name: {$result->get_timezone_name() }\n";
        
        // Get current time
        $datetime = $result->get_datetime();
        echo "Local Time: {$datetime->format( 'Y-m-d H:i:s') }\n";
        
        // Get offset information
        echo "UTC Offset: {$result->get_formatted_offset() }\n";
        if ( $result->is_dst() ) {
            echo "DST is in effect\n";
        }
    }
}
```

## Use Cases

* **Local Time Display**: Show accurate local times
* **Event Scheduling**: Convert times between zones
* **Time-based Features**: Schedule operations in local time
* **Global Applications**: Handle international time differences
* **DST Handling**: Accurate seasonal time adjustments
* **Time Formatting**: Display times in local formats
* **Location Services**: Time-aware location features
* **Data Timestamps**: Convert timestamps to local time

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/google-timezone)
- [Issue Tracker](https://github.com/arraypress/google-timezone/issues)