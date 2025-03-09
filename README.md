# Google Reviews Importer

A WordPress plugin that automatically imports Google reviews as testimonials, designed specifically to work with Advanced Custom Fields and Bricks Builder.

**Author:** Richard Peirce (https://rnr.design)

## Features

- Automatically imports Google reviews on a monthly schedule
- Creates custom testimonial posts with structured review data
- Admin dashboard widget to monitor plugin health
- Import history tracking with detailed logs
- GitHub integration for automatic health checks
- Seamless integration with Bricks Builder
- Compatible with Advanced Custom Fields (required)

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Advanced Custom Fields plugin (Free or Pro)
- Google Places API key with proper permissions
- Bricks Builder (optional, but fully supported)

## Installation

1. Upload the `google-reviews-importer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Google Reviews to configure your API key and Place ID
4. Make sure Advanced Custom Fields is installed and activated
5. The plugin will automatically check for the required ACF fields

## ACF Fields Setup

The plugin requires specific ACF fields for the testimonial post type. You have two options for setting these up:

### Option 1: Automatic Setup
The plugin includes ACF field configurations that can be automatically loaded. If the required fields are missing, you'll see a notification in the plugin settings.

### Option 2: Manual ACF Setup
If you prefer to set up the fields manually, create a new ACF Field Group with these fields:

1. **Testimonial Title** (Text)
   - Field Name: `testimonial_title`
   - Required: Yes

2. **Testimonial Summary** (Textarea)
   - Field Name: `testimonial_summary`
   - Required: No

3. **Testimonial** (Textarea)
   - Field Name: `testimonial`
   - Required: Yes

4. **Date** (Date Picker)
   - Field Name: `testimonial_date`
   - Required: No

5. **Rating** (Select)
   - Field Name: `testimonial_rating`
   - Choices: 1-5 stars

6. **Source** (Select)
   - Field Name: `testimonial_source`
   - Default Value: Google

Set the location rule to show this field group when Post Type is equal to "testimonial".

## Bricks Builder Integration

This plugin works seamlessly with Bricks Builder:

1. **Dynamic Data** - Access all review fields through Bricks' dynamic data system
2. **Custom Templates** - Create custom templates for displaying testimonials
3. **Query Loop** - Use query loops to display multiple reviews with filtering options
4. **Star Ratings** - Easily display star ratings using the rating field

Example Bricks Builder setup:
- Create a template for the testimonial post type
- Add a query loop to display multiple testimonials
- Use dynamic data to display reviewer name, date, rating, and review text
- Filter by rating or date using Bricks query filters

## GitHub Monitoring Setup (Optional)

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

## Troubleshooting

If you encounter issues:

- **ACF Fields Not Working**: Ensure ACF is active and field names match what the plugin expects
- **No Reviews Found**: Verify your Place ID is correct and has reviews
- **API Connection Errors**: Check that your API key has the Places API enabled
- **CRON Not Running**: WordPress CRON requires site visits to trigger - consider setting up a server cron

## Customization

You can customize:
- The frequency of imports by modifying the CRON schedule
- The post type used for testimonials (defaults to "testimonial")
- The ACF field names and types

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Richard Peirce (https://rnr.design)
