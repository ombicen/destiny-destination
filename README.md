# Destiny Destination Widget

An Elementor widget that displays real-time distance and travel time to **Vallentuna bil och däckservice** (Moränvägen 13, 186 40 Vallentuna, Sweden).

## Features

- 🗺️ **GPS Location Detection**: Automatically uses the visitor's current location when available
- 📍 **Fallback Address**: Configurable fallback address when GPS is not available or denied
- ⏱️ **Real-time Data**: Integration with Google Maps Distance Matrix API for accurate results
- 🎨 **Customizable**: Full Elementor styling controls and options
- 📱 **Responsive**: Mobile-friendly design with dark mode support
- 🔄 **Auto-refresh**: Intelligent caching and refresh on page visibility changes

## Installation

1. Upload the plugin files to `/wp-content/plugins/destiny-destination/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Settings > Destiny Destination** to configure the Google Maps API key (optional)

## Configuration

### Google Maps API Key (Optional)

For real-time distance and duration data:

1. Get a Google Maps API key from the [Google Cloud Console](https://console.cloud.google.com/)
2. Enable the **Distance Matrix API** for your project
3. Go to **WordPress Admin > Settings > Destiny Destination**
4. Enter your API key and save

**Note**: Without an API key, the widget will display realistic mock data for testing purposes.

### Required APIs
- Distance Matrix API

## Usage

1. Edit any page with Elementor
2. Search for "Destination Info" in the widget panel
3. Drag the widget to your desired location
4. Configure the widget settings:
   - **Title**: Customize the widget title
   - **Fallback Source Address**: Address to use when GPS is unavailable
   - **Show Distance**: Toggle distance display
   - **Show Duration**: Toggle travel time display
5. Style the widget using Elementor's styling controls
6. Save and view your page

## Widget Settings

### Content Tab
- **Title**: Widget heading text
- **Fallback Source Address**: Default location when GPS is unavailable (default: "Stockholm, Sweden")
- **Show Distance**: Toggle to show/hide distance information
- **Show Duration**: Toggle to show/hide travel time information

### Style Tab
- **Text Color**: Customize the text color
- **Typography**: Font family, size, weight, etc.

## Technical Details

### Destination
- **Business**: Vallentuna bil och däckservice
- **Address**: Moränvägen 13, 186 40 Vallentuna, Sweden

### How it Works

1. **Location Detection**: The widget first attempts to get the user's GPS coordinates
2. **Fallback**: If GPS fails or is denied, it uses the configured fallback address
3. **API Call**: Makes a request to Google Maps Distance Matrix API (or returns mock data)
4. **Display**: Shows formatted distance and travel time with loading states and error handling

### File Structure

```
destiny-destination/
├── destiny-destination.php          # Main plugin file
├── widgets/
│   └── destination-widget.php       # Elementor widget class
├── includes/
│   └── class-google-maps-api.php    # Google Maps API integration
├── assets/
│   ├── css/
│   │   └── destiny-destination.css  # Widget styles
│   └── js/
│       ├── destiny-destination.js   # Main JavaScript
│       └── destiny-destination-frontend.js # Frontend utilities
└── README.md                        # This file
```

## Browser Support

- Modern browsers with Geolocation API support
- Graceful fallback for browsers without GPS capability
- Responsive design for mobile and desktop

## Privacy

- GPS location is only used temporarily for distance calculation
- No location data is stored on the server
- Users can deny location permission and still use the fallback address

## Troubleshooting

### Widget shows "Unable to get location information"
- Check if Google Maps API key is configured correctly
- Verify the Distance Matrix API is enabled in Google Cloud Console
- Check browser console for JavaScript errors

### GPS location not working
- Ensure the website is served over HTTPS (required for Geolocation API)
- Users must grant location permission in their browser
- The fallback address will be used if GPS fails

### Mock data always showing
- Configure a valid Google Maps API key in plugin settings
- Ensure the API key has Distance Matrix API enabled
- Check API quotas and billing in Google Cloud Console

## Development

### Hooks and Filters

The plugin provides several hooks for developers:

```php
// Filter the destination address
add_filter('destiny_destination_address', function($address) {
    return 'Custom Address, City, Country';
});

// Filter API response
add_filter('destiny_destination_api_response', function($response, $origin) {
    // Modify response data
    return $response;
}, 10, 2);
```

### Extending the Widget

You can extend the widget functionality by:

1. Creating child themes with custom CSS
2. Using WordPress hooks to modify behavior
3. Forking the plugin for custom modifications

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and bug reports, please contact the plugin developer or create an issue in the project repository.

## Changelog

### Version 1.0.0
- Initial release
- Google Maps Distance Matrix API integration
- Geolocation support with fallback addresses
- Full Elementor integration with styling controls
- Responsive design with dark mode support
- Mock data for testing without API key 