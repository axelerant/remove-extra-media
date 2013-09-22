=== Remove Extra Media ===

Contributors: comprock
Donate link: http://aihr.us/about-aihrus/donate/
Tags: remove, media, posts
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use this tool to remove extra media attachments from your selected post types.

== Description ==

Use this tool to remove extra media attachments from your selected post types.

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

= API =

* Read the [Remove Extra Media API](https://github.com/michael-cannon/remove-extra-media/blob/master/API.md).

= Languages =

You can translate this plugin into your own language if it's not done so already. The localization file `remove-extra-media.pot` can be found in the `languages` folder of this plugin. After translation, please [send the localized file](http://aihr.us/contact-aihrus/) to the plugin author.

See the FAQ for further localization tips.

= Support =

Please visit the [Remove Extra Media Knowledge Base](https://aihrus.zendesk.com/categories/20128436-Remove-Extra-Media) for frequently asked questions, offering ideas, or getting support.

If you want to contribute and I hope you do, visit the [Remove Extra Media Github repository](https://github.com/michael-cannon/remove-extra-media).


== Installation ==

1. Via WordPress Admin > Plugins > Add New, Upload the `remove-extra-media.zip` file
1. Alternately, via FTP, upload `remove-extra-media` directory to the `/wp-content/plugins/` directory
1. Activate the 'Remove Extra Media' plugin after uploading or through WordPress Admin > Plugins


== Frequently Asked Questions ==

Please visit the [Remove Extra Media Knowledge Base](https://aihrus.zendesk.com/categories/20128436-Remove-Extra-Media) for frequently asked questions, offering ideas, or getting support.


== Screenshots ==

1. Settings screen
2. Process screen pre-run
3. Process screen post-run


== Changelog ==

See [Changelog](https://github.com/michael-cannon/remove-extra-media/blob/master/CHANGELOG.md)


== Upgrade Notice ==

= 1.0.0 =

* Initial release


== Beta Testers Needed ==

I really want Remove Extra Media and Remove Extra Media Premium to be the best WordPress plugins of their type. However, it's beyond me to do it alone.

I need beta testers to help with ensuring pending releases of Remove Extra Media and Remove Extra Media Premium are solid. This would benefit us all by helping reduce the number of releases and raise code quality.

[Please contact me directly](http://aihr.us/contact-aihrus/).

Beta testers benefit directly with latest versions, a free 1-site license for Remove Extra Media Premium, and personalized support assistance.

== TODO ==

See [TODO](https://github.com/michael-cannon/remove-extra-media/blob/master/TODO.md)
