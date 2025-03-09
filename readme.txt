=== Google Reviews Importer ===
Contributors: Reflect + Refine
Tags: google, reviews, testimonials, google places, bricks builder
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author: Richard Peirce
Author URI: https://rnr.design

Automatically imports Google reviews as testimonials for your WordPress website, with specific support for Advanced Custom Fields and Bricks Builder.

== Description ==

Google Reviews Importer automatically fetches reviews from your Google Business Profile and imports them as testimonials on your WordPress site. The plugin is designed to work seamlessly with Advanced Custom Fields and Bricks Builder.

= Features =
* Automatic monthly import of new Google reviews
* Manual import option through admin dashboard
* Dashboard widget for monitoring plugin health
* Import history tracking with detailed logs
* Complete integration with Advanced Custom Fields
* Compatible with Bricks Builder for dynamic display
* GitHub integration for automated health monitoring

= Requirements =
* WordPress 5.0 or higher
* PHP 7.4 or higher
* Advanced Custom Fields plugin (Pro or Free)
* Google Places API key with proper permissions
* Bricks Builder (optional, but fully supported)

= Bricks Builder Integration =
This plugin is specifically designed to work with Bricks Builder. The testimonials are imported as a custom post type with ACF fields that can be easily accessed through Bricks' dynamic data system. You can create custom templates in Bricks to display the reviews using fields like:

* Testimonial title (reviewer name)
* Testimonial content (review text)
* Rating (1-5 stars)
* Date
* Source (automatically set to "Google")

= ACF Integration =
This plugin requires Advanced Custom Fields to store the structured review data. The necessary field configuration is included with the plugin and can be automatically imported.

== Installation ==

1. Upload the `google-reviews-importer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Google Reviews to configure your API key and Place ID
4. Make sure Advanced Custom Fields is installed and activated
5. The plugin will automatically set up the required ACF fields, or you can import them manually through ACF > Tools

== Frequently Asked Questions ==

= Do I need Advanced Custom Fields? =

Yes, this plugin requires Advanced Custom Fields (either free or Pro version) to store the review data in a structured format.

= Is this compatible with Bricks Builder? =

Yes! This plugin was specifically designed to work with Bricks Builder. The testimonials are stored in a way that makes them easy to access through Bricks' dynamic data features.

= Do I need a Google API key? =

Yes, you need a Google API key with the Places API enabled. Visit the Google Cloud Console to create one.

= How often does the plugin import reviews? =

By default, the plugin checks for new reviews once a month. You can also import reviews manually at any time through the settings page.

= Can I use this with other page builders? =

While specifically optimized for Bricks Builder, the plugin will work with any page builder that can access custom post types and ACF fields, including Elementor, Beaver Builder, and Oxygen.

== Screenshots ==

1. Plugin settings page
2. Dashboard health widget
3. Import history
4. Bricks Builder integration example

== Changelog ==

= 1.0.1 =
* Added health monitoring dashboard widget
* Added import history tracking
* Improved error handling
* Added Bricks Builder compatibility notes

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.1 =
Added health monitoring, import history tracking, and improved Bricks Builder compatibility.
