=== leenk.me ===
Contributors: layotte
Tags: twitter, facebook, google, google buzz, oauth, profile, fan page, image, images, social network, social media, post, posts, twitter post, tinyurl, twitter friendly links, admin, authors, contributors, exclude, category, categories, retweet, republish, rebuzz, connect, status update, leenk.me, leenk me, leenk, scheduled post
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 1.1.3

leenk.me empowers you to publish to your Twitter, Facebook, & Google Buzz account whenever you publish a new post in WordPress.

== Description ==

leenk.me automatically publishes a tweet to your Twitter account, a status update to your Facebook profile and/or Fan Page, and a Buzz to your Google Buzz profile whenever you publish a new post in your WordPress website.

What can you do with leenk.me?

* Publish automatically to your social networks when you publish a new post in WordPress.
* Choose which categories are published automatically.
* Exclude individual posts from being published to your social networks.
* Automatically post to your social networks when a scheduled post is published.
* Resend previously published posts to your social networks.
* Additional authors can setup their own leenk.me accounts and add them to your website.
* Publish to all author's leenk.me accounts whenever a post is published.
* Customize your Tweet format with the custom tags %TITLE% and %URL%
* Automatically shorten URLs with [Twitter Friendly Links](http://wordpress.org/extend/plugins/twitter-friendly-links/), if it is installed - otherwise leenk.me uses TinyURL.
* Customize your Buzz message for individual posts.

What sets leenk.me apart from others?

* Extremely easy to use and setup, essentially a "set it and forget it" service.
* leenk.me uses the most secure APIs available to connect to your social networks.
* leenk.me doesn't need to save your social network passwords.
* You authorize which applications are connected to your leenk.me account.

[Support](http://leenk.me/contact)

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

= Where can I find additional help or make suggestions? =

Feel free to use the leenk.me [contact form](http://leenk.me/contact) and we will respond as soon as possible.

== Changelog ==
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

== Screenshots ==

1. leenk.me plugin settings page
2. verify your leenk.me API key
3. Enable Twitter, Facebook, and Google Buzz plugins
4. Update your Twitter settings
5. Update your Facebook settings
6. Update your Google Buzz settings
7. ReTweet, RePublish, ReBuzz row actions
8. Post specific settings for Twitter
9. Post specific settings for Facebook
10. Post specific settings for Google Buzz