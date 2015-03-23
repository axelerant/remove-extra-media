=== Remove Extra Media ===

Contributors: comprock, saurabhd
Donate link: http://axelerant.com/about-axelerant/donate/
Tags: remove, media, posts
Requires at least: 3.5
Tested up to: 3.8.0
Stable tag: 1.1.0RC1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use Remove Extra Media to remove extra media attachments from your selected post types.

== Description ==

Use Remove Extra Media to remove extra media attachments from your selected post types.

In my use case, my testimonials widgets post types in some cases, for whatever reason, had up to 7 extra media attachments related to them. I wanted a quick way to clear those excess out. Considering that the only image or media attachment a testimonial should have was the featured, I simply removed all the others.

This tool works by removing the attachment relationship as this saves the media file in case it's used by other attachments. Using `wp_delete_attachment` would delete the media file, which is something to avoid.

= Primary Features =

* API
* Ajax based processing screen
* Media isn't deleted, only unattached from post type entry
* Settings export/import
* Settings screen
* Variable allowed media count

= Settings Options =

**General**

* Post Type - Post type to remove excess media from.
* Media Limit - Number of media items to limit selected post types to. Count includes featured image.

**Testing**

* Debug Mode - Bypass Ajax controller to handle posts_to_import directly for testing purposes.
* Posts to Import - A CSV list of post ids to import, like '1,2,3'.
* Skip Importing Posts - A CSV list of post ids to not import, like '1,2,3'.
* Import Limit - Useful for testing import on a limited amount of posts. 0 or blank means unlimited.

**Compatibility & Reset**

* Export Settings – These are your current settings in a serialized format. Copy the contents to make a backup of your settings.
* Import Settings – Paste new serialized settings here to overwrite your current configuration.
* Remove Plugin Data on Deletion? - Delete all Remove Extra Media data and options from database on plugin deletion
* Reset to Defaults? – Check this box to reset options to their defaults


== Installation ==

= Requirements =

* TBD

= Install Methods =

* Through WordPress Admin > Plugins > Add New, Search for "Remove Extra Media"
	* Find "Remove Extra Media"
	* Click "Install Now" of "Remove Extra Media"
* Download [`remove-extra-media.zip`](http://downloads.wordpress.org/plugin/remove-extra-media.zip) locally
	* Through WordPress Admin > Plugins > Add New
	* Click Upload
	* "Choose File" `remove-extra-media.zip`
	* Click "Install Now"
* Download and unzip [`remove-extra-media.zip`](http://downloads.wordpress.org/plugin/remove-extra-media.zip) locally
	* Using FTP, upload directory `remove-extra-media` to your website's `/wp-content/plugins/` directory

= Activation Options =

* Activate the "Remove Extra Media" plugin after uploading
* Activate the "Remove Extra Media" plugin through WordPress Admin > Plugins

= Usage =

1. Configure through WordPress Admin > Settings > Remove Extra Media
1. Process posts and such via WordPress Admin > Tools > Remove Extra Media

= Upgrading =

* Through WordPress
	* Via WordPress Admin > Dashboard > Updates, click "Check Again"
	* Select plugins for update, click "Update Plugins"
* Using FTP
	* Download and unzip [`remove-extra-media.zip`](http://downloads.wordpress.org/plugin/remove-extra-media.zip) locally
	* Upload directory `remove-extra-media` to your website's `/wp-content/plugins/` directory
	* Be sure to overwrite your existing `remove-extra-media` folder contents


== Frequently Asked Questions ==

= Most Common Issues =

* Got `Parse error: syntax error, unexpected T_STATIC, expecting ')'`? Read [Most Axelerant Plugins Require PHP 5.3+](https://nodedesk.zendesk.com/hc/en-us/articles/202331041) for the fixes.
* [Debug common theme and plugin conflicts](https://nodedesk.zendesk.com/hc/en-us/articles/202330781)
* [Change or debug CSS](https://nodedesk.zendesk.com/hc/en-us/articles/202243372)

= Still Stuck or Want Something Done? Get Support! =

1. [Remove Extra Media Knowledge Base](https://nodedesk.zendesk.com/hc/en-us/sections/200861112-WordPress-FAQs) - read and comment upon frequently asked questions
1. [Open Remove Extra Media Issues](https://github.com/michael-cannon/remove-extra-media/issues) - review and submit bug reports and enhancement requests
1. [Remove Extra Media Support on WordPress](http://wordpress.org/support/plugin/remove-extra-media) - ask questions and review responses
1. [Contribute Code to Remove Extra Media](https://github.com/michael-cannon/remove-extra-media/blob/master/CONTRIBUTING.md)
1. [Beta Testers Needed](http://axelerant.com/become-beta-tester/) - get the latest Remove Extra Media version


== Screenshots ==

1. Settings screen
2. Process screen pre-run
3. Process screen post-run

[gallery]


== Changelog ==

See [Changelog](https://github.com/michael-cannon/remove-extra-media/blob/master/CHANGELOG.md)


== Upgrade Notice ==

= 1.0.0 =

* Initial release


== Notes ==

TBD


== API ==

* Read the [Remove Extra Media API](https://github.com/michael-cannon/remove-extra-media/blob/master/API.md).


== Localization ==

You can translate this plugin into your own language if it's not done so already. The localization file `remove-extra-media.pot` can be found in the `languages` folder of this plugin. After translation, please [send the localized file](http://axelerant.com/contact-axelerant/) for plugin inclusion.

**[How do I localize?](https://nodedesk.zendesk.com/hc/en-us/articles/202294892)**

* Serbian by [Ognjen Djuraskovic](http://firstsiteguide.com)
* Spanish by [Ognjen Djuraskovic](http://firstsiteguide.com)
