# Google Reviews Importer

A WordPress plugin that automatically imports Google reviews as custom testimonials.

## Features

- Automatically imports Google reviews on a monthly schedule
- Creates custom testimonial posts with review data
- Admin dashboard widget to monitor plugin health
- Import history tracking
- Manual import option for immediate updates
- GitHub integration for automatic health checks

## Setting Up GitHub Monitoring (Optional)

To enable automated health checks for the plugin:

1. Fork this repository to your own GitHub account
2. Go to your forked repository's Settings > Secrets and variables > Actions
3. Add the following secrets:
   - `GOOGLE_API_KEY`: Your Google API key with Places API enabled
   - `GOOGLE_PLACE_ID`: Your business's Google Place ID
   - `MAIL_SERVER`: Your SMTP server (e.g., smtp.gmail.com)
   - `MAIL_PORT`: SMTP port (e.g., 587)
   - `MAIL_USERNAME`: Email username
   - `MAIL_PASSWORD`: Email password or app password
   - `NOTIFICATION_EMAIL`: Email where you want to receive alerts

These credentials will be used only in your private fork for monitoring your specific installation.

## Installation

1. Upload the `google-reviews-importer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Google Reviews to configure your API key and Place ID

## Configuration

### Required Settings

- **Google API Key**: Your Google Cloud Platform API key with Places API enabled
- **Google Place ID**: The unique identifier for your business location

### Setting Up Google API Key

1. Visit the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Places API
4. Create an API key and restrict it to the Places API
5. Add HTTP referrer restrictions to limit usage to your domain

## Usage

The plugin will automatically import new Google reviews once a month. You can also:

- Run a manual import from the Settings > Google Reviews page
- Check plugin health from the WordPress dashboard
- View import history on the settings page

## GitHub Integration

This plugin includes GitHub Actions workflows for automated health checks. To set this up:

1. Create a GitHub repository for your plugin
2. Add the plugin files to the repository
3. Create a `.github/workflows` directory
4. Copy the workflow file to that directory
5. Set up the following GitHub secrets:
   - `GOOGLE_API_KEY`: Your Google API key
   - `GOOGLE_PLACE_ID`: Your Google Place ID
   - `MAIL_SERVER`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`: SMTP details for notifications
   - `NOTIFICATION_EMAIL`: Email to receive alerts

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Advanced Custom Fields plugin
- Testimonial post type (can be created separately)

## Troubleshooting

### Common Issues

- **API Key Errors**: Make sure your API key is valid and has the Places API enabled
- **No Reviews Found**: Verify your Place ID is correct
- **Import Not Running**: Check your WordPress cron is functioning properly

### Debug Mode

For troubleshooting, you can enable more detailed logging by adding this to your wp-config.php:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Maintenance

This plugin should be checked regularly for:

1. Google API changes
2. WordPress compatibility updates
3. PHP version compatibility
4. Security improvements

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Reflect + Refine
