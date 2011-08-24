=== leenk.me ===
Contributors: layotte
Tags: twitter, facebook, face, book, googlebuzz, google, buzz, linkedin, linked, in, friendfeed, friend, feed, oauth, profile, fan page, groups, image, images, social network, social media, post, page, custom post type, twitter post, tinyurl, twitter friendly links, admin, author, contributor, exclude, category, categories, retweet, republish, rebuzz, connect, status update, leenk.me, leenk me, leenk, scheduled post, publish, publicize, smo, social media optimization, ssl, secure, facepress, hashtags, hashtag, categories, tags, social tools, bit.ly, j.mp
Requires at least: 2.8
Tested up to: 3.2
Stable tag: 1.3.6.1

leenk.me empowers you to publicize your WordPress content to your Twitter, Facebook, Google Buzz, LinkedIn, & FriendFeed accounts automatically.

== Description ==

leenk.me automatically publishes a tweet to your Twitter account, a status update to your Facebook profile/fan page/group walls, a buzz to your Google Buzz profile, a share on your LinkedIn profile, and an entry on your FriendFeed profile/group whenever you publish a new post in your WordPress website.

What can you do with leenk.me?

* Publish automatically to your social networks when you publish a new post in WordPress.
* Works with Posts, Pages, and Custom Post Types. (Requires WP 2.9+ for Posts and 3.0+ for Custom Post Types)
* Choose which categories are published automatically.
* Exclude individual posts from being published to your social networks.
* Automatically post to your social networks when a scheduled post is published.
* Resend previously published posts to your social networks.
* Additional authors can setup their own leenk.me accounts and add them to your WordPress dashboard.
* Publish to all author's leenk.me accounts whenever a post is published.
* Customize your Tweet format with the custom variables %TITLE%, %URL%, %CATS%, and %TAGS%.
* Automatically shorten URLs with [Twitter Friendly Links](http://wordpress.org/extend/plugins/twitter-friendly-links/), if it is installed - otherwise leenk.me uses TinyURL or WordPress's default short URL.
* Add a filter to [use your own custom URL shortener](http://leenk.me/2011/03/22/how-to-use-the-bit-ly-url-shortener-in-leenk-me/), like bit.ly
* Customize your Buzz message for individual posts.
* Customize your Facebook post for individual posts.
* Customize your LinkedIn shares for individual posts.
* Customize your FriendFeed entries for individual posts.

What sets leenk.me apart from others?

* Extremely easy to use and setup, essentially a "set it and forget it" service.
* Highly customizable!
* leenk.me uses the most secure APIs available to connect to your social networks.
* leenk.me doesn't need to save your social network passwords.
* You authorize which applications are connected to your leenk.me account.
* leenk.me is instant, no need to wait for an RSS reader to get your latest posts... publishing sends your content to leenk.me automatically.

You will need a [leenk.me API key](http://leenk.me) to use the leenk.me plugin. The leenk.me subscription is 99 cents a month with a one month free trial. Cancel anytime before your trial ends and you won't be charged. This small subscription fee is used to maintain the leenk.me server and for continued development/support.

Have questions? Contact me through the leenk.me [support](http://leenk.me/contact) form.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `leenkme` directory to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create a leenk.me account [here](http://leenk.me) to get a leenk.me API and to set up your social networking options.
1. Update the leenk.me plugin options with your leenk.me API Key and set any plugin options. 
1. Next time you publish a new post it will auotomatically be sent to leenk.me and distributed to your social networks.

== Frequently Asked Questions ==

= Does leenk.me work with scheduled posts? =

Yes, the leenk.me plugin hooks into the schedule-to-publish action that WordPress uses when publishing a scheduled post.

= Does leenk.me work with multiple authors? =

Yes, just be sure to check the box to Tweet/Publish/Buzz all authors in the leenk.me plugins.

= Can I add multiple Twitter/Facebook/Buzz accounts to a single leenk.me account? =

Unfortunately this is not possible at this time. If you need multiple of a single social network, you'll need to sign up for another leenk.me account. Feel free to contact us if you need any help with this.

= How to use the leenk.me Twitter plugin =

http://leenk.me/2010/09/04/how-to-use-the-leenk-me-twitter-plugin-for-wordpress/

= How to use the leenk.me Facebook plugin =

http://leenk.me/2010/09/04/how-to-use-the-leenk-me-facebook-plugin-for-wordpress/

= How to use the leenk.me Google Buzz plugin =

http://leenk.me/2010/09/05/how-to-use-the-leenk-me-google-buzz-plugin-for-wordpress/

= How to use the leenk.me LinkedIn plugin =

http://leenk.me/2010/12/01/how-to-use-the-leenk-me-linkedin-plugin-for-wordpress/

= How to use the leenk.me FriendFeed plugin =

http://leenk.me/2011/04/08/how-to-use-the-leenk-me-friendfeed-plugin-for-wordpress/

= Where can I find additional help or make suggestions? =

Feel free to use the leenk.me [contact form](http://leenk.me/contact) and we will respond as soon as possible.

= Can I use other URL shorteners? =

Yes, there is not a GUI interface for this yet, but if your URL shortener has a basic REST API then you can use the WordPress action hook 'leenkme_url_shortener' to change the URL shortener used. I wrote a post detailing [how to use the bit.ly URL shortener](http://leenk.me/2011/03/22/how-to-use-the-bit-ly-url-shortener-in-leenk-me/).

== Changelog ==
= 1.3.6.1 =
* Merge bug in 1.3.6 with Google Buzz

= 1.3.6 =
* Fixed exclusion bug for Facebook and Friendfeed
* Added links to Settings pages in more obvious location on General Settings page.
* Renamed "leenk.me" suboption to "leenk.me Settings"
* Update for multi-author publishing

= 1.3.5 =
* Fixed bug caused by new versom of jQuery in WP 3.2 when testing Facebook and Friendfeed
* Added efficiency updates for blogs with large userbase

= 1.3.4 =
* Fixed bug with %CATS% for Twitter, if using really long category names causing an infinite loop and eventual timeout

= 1.3.3 =
* Changed how the meta boxes are displayed, switched to the native WordPress meta box functions
* Changed how leenk.me uses the Http class, by using the built-in wp_remote_* functions
* Fixed bug for getting shorturl from WordPress if permalinks are turned off (and website cannot contact TinyURL), also submitted patch to core to ultimately fix this problem
* Updated leenk.me settings pages
* Updated leenk.me icon

= 1.3.2 =
* Added user_can() function for backwards compatibility for WP3.0 and below
* Added clean_user_cache() function for backwards compatibility for WP2.9 and below

= 1.3.1 =
* Fixed memory_limit issue when doing upgrade to 1.3.0 for sites with large number of users

= 1.3.0 =
* Added FriendFeed support!
* Updated the category include/exclude option for each page
* Automatically enable the "All Authors?" option on new activations for sites that have multiple users setup
* Fixed error introduced with the share link feature on the Facebook module
* General security hardening
* Fixed bug causing leenk.me post meta to be erased when you quick-edit a post
* Fixed JavaScript bug that prevents the "Re" functions from working immediately after quick-editing post info
* Fixed Tweet Category/Tag warnings
* Added disabled() helper function for WP2.9.2 and below

= 1.2.13 =
* Bug fix for Twitter Friendly Links URL shortener (again)

= 1.2.12 =
* Bug fix for Twitter Friendly Links URL shortener

= 1.2.11 =
* Added support for custom URL shorteners, like bit.ly, j.mp and any others that you want to add.

= 1.2.10 =
* Updated support contact information

= 1.2.9 =
* Fixed small bug causing Facebook descriptions to not be truncated.

= 1.2.8 =
* Facebook added a new "links" method to the API. Added option to use it instead of the normal "feeds" method.
* Facebook also started restricting Message and Description length. Added restriction back into plugin (420 charactesr for Message, 300 characters for description).

= 1.2.7 =
* Squashed bug causing non-thumbnail posts to have a thumbnail associated with them from the Media Library.

= 1.2.6 =
* Added filters, 'facebook_image' and 'linkedin_image' to override the image used when posting to Facebook and LinkedIn.
* Squashed bug causing twitter to improperly calculate the Tweet length for Russian characters.
* Squashed bug causing twitter to improperly calculate the Tweet length for long titles.

= 1.2.5 =
* Added auto-hashtagging variables for categories and tags in the Tweet Format.

= 1.2.4 =
* Fixed bug causing "Pending Review" to "Publish" posts to be ignored by leenk.me.

= 1.2.3 =
* Added ability to publish to Facebook Groups
* Added ability to restrict certain WordPress roles from access the plugin settings - two new roles were created, "leenkme_manage_all_settings" for Administrators, and 'leenkme_edit_user_settings' for Editors/Authors/Contributors.

= 1.2.2 =
* Changed the way the leenk.me _wpnonce fields are handled, previously it was causing an overload in the $_GET structure for some WordPress sites when filtering and/or searching posts in the WordPress Dashboard.

= 1.2.1 =
* Removed screenshots from plugin, seems to be causing an issue with WordPress updating.

= 1.2.0 =
* Added LinkedIn support!
* Tested on WordPress 2.8.6, 2.9.2, and 3.0.2.

= 1.1.9 =
* Fixed function call in Twitter module

= 1.1.8 =
* Fixed bug causing shorttags showing up in Facebook and Google Buzz posts.
* Fixed category inclusion/exclusions bug.
* Added Page and Custom Post Type support.

= 1.1.7 =
* Receiving reports of users with old version of cURL installed, they are not able to verify the SSL (because it is too new). Set the API call to not try to verify the SSL, the SSL still works and encrypts the content though.

= 1.1.6 =
* Added SSL to leenk.me API for increased security

= 1.1.5 =
* Added more features to Facebook plugin, now you can truly customize your Facebook posts.
* Added support links to each social network module.
* Updated API to fix slashes being included in Facebook posts.
* Updated Facebook screenshots.

= 1.1.4 =
* Fixed problem caused by users not saving Twitter/Facebook/Google Buzz settings before trying to use the plugin
* Set "Post to Profile" as initial default for Facebook settings
* Removed superfulous get_user_option call for Google Buzz post meta.

= 1.1.3 =
* Added Google Buzz support!
* Fixed some table formatting for the custom post boxes.
* Added additional error reporting.
* Fixed problem with post meta being deleted during autosave process.
* Fixed bug caused by new users activating leenk.me for the first time and not setting their default settings.
* Made some more efficiency updates, especially for multi-author sites.
* Updated screenshots.
* Tested on WordPress 2.8.6, 2.9.2, and 3.0.1.

= 1.1.2 =
* Fixed bug causing Facebook posts to not have a "description" if there was no excerpt defined in WordPress.
* Tested on WordPress 2.8.6, 2.9.2, and 3.0.1.

= 1.1.1 =
* Removed unnecessary CSS and images.
* Changed leenk.me main settings page to reduce CSS bloat.
* Re-organized the Twitter and Facebook code to be a little more efficient for multi-user setups.
* Increased error checking.
* Tested on WordPress 2.8.6, 2.9.2, and 3.0.1.

= 1.1.0 =
* Efficiency updates, now the leenk.me plugin only contacts leenk.me 1 time per user (instead of 1 time per social network).
* Updated leenk.me API from 1.0 to 1.1, to assist with efficiency and better error reporting.
* Fixed duplicate leenk.me screen in WP2.8.x (though I recommend upgrading to the latest version).
* Updated AJAX popup box, made it wider and higher.

= 1.0.2 =
* Fixed bug caused by not having thumbnails enabled in WP2.8.x and WP2.9.x.

= 1.0.1 =
* Fixed small bug: removed default check from Facebook profile for new plugin activations.

= 1.0.0 = 
* leenk.me is a fork from [Twitter Post](http://wordpress.org/extend/plugins/rf-twitterpost/), which unfortunately is no longer supported because Twitter deactivated their REST API.