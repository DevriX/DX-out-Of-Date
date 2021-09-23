=== DX Out of Date ===
Contributors: nofearinc, devrix
Tags: posts, out of date, outdated, label, message, box, old, posts
Requires at least: 4.7
Tested up to: 5.8
Stable tag: 1.0.1
Requires PHP: 7.1 or higher
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display a notification box on the single view of your outdated posts, given a set amount of time (configurable).

== Description ==

DX Out of Date allows you to display a notification box on your posts 
when a given amount of time has passed. Quite handy when writing tutorials
 or any content that might get outdated due to external factors - now you
 could automatically notify your readers after a given period.

You could configure the expiration time of your posts in the admin panel. Valid time units are days, months and years.

Currently the plugin options are:

* Duration frame (days, months, years)
* Period - "amount" of "duration" units (1 to 40)
* Message - the message format for the notification box
* Enable on template - a checkbox for displaying the outdated posts in the template. Must be checked to enable the plugin functionality
* Skin manager - a list with skins for customizing the notification

== Screenshots ==
1. DX Out of Date Settings page.
2. Out of date message in old post.

== Installation ==

1. Upload the `dx-out-of-date` folder in the `/wp-content/plugins/` directory
1. Activate the `DX Out of Date` plugin through the 'Plugins' menu in WordPress
1. Go to Settings -> DX Out of Date
1. Configure your time settings and check the "Enable the message by default on all outdated posts " checkbox
1. Save the form and you're good to go!

Other ways to display the "Out of Date" box snippet:

1. Use the `[out_of_date]` shortcode
1. Call the DX_Out_Of_Date::outdated_box_generator() function right before rendering the post

== Frequently Asked Questions ==

= How to configure the duration/period settings? =

These are the options that define what an "outdated" post means. For instance, selecting `months` in `Duration frame` and `6` in `Period`
means that every post older than 6 months would be qualified as "outdated".

= Can I add new skins? =

Not without some small plugin modifications. However, the `clean` skin adds no styles at all, which means that you could
 easily style the `.out-of-date` class in the frontend in your custom theme stylesheet.
 
= Why would I need that plugin? =

If you write tutorials or articles related to products/services in a given state, 
updating them could affect the validity of your resources. A good example is one of
 the resources that led to building the plugin - [Brandon's blog](http://www.brandonsavage.net/making-life-better-with-the-spl-autoloader/),
with his custom approach for marking tutorials as outdated.

== Screenshots ==

1. Admin options page
2. Sample view of an outdated post

== Changelog ==

= 1.0.1 =
* Fixed notice when banner is not visible 

= 1.0 =
* Added new section - Enable the display of the status in post 
* Refactor section - Choose a skin for your template
* Added new section - Choose the position of the message

= 0.3 =
* First stable plugin version
* Admin settings
* Single view display
* Skins
* Time options

== Upgrade Notice ==
This version includes some new fresh images and new options
