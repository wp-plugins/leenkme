=== leenk.me ===
Contributors: layotte
Tags: twitter, facebook, oauth, profile, pages, social networking, social media, posts, twitter post, tinyurl, twitter friendly links, multiple authors, exclude post, category, categories, retweet, republish, javascript, ajax, connect, status update, leenk.me, leenk, leenk me
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 1.1.1

The leenk.me plugin empowers you to publish to your Twitter and Facebook accounts whenever you publish a new post in WordPress. leenk.me uses the most secure APIs available to ensure you are able to post content to your social networks.

== Description ==

leenk.me automatically publishes a tweet to your Twitter account and a status update to your Facebook profile and/or page whenever you publish a new post in your WordPress website.

What can you do with leenk.me?

* Publish automatically to your social networks when you publish a new post in WordPress.
* Choose which categories are published automatically.
* Exclude individual posts from being published to your various social networks.
* Resend previously published posts to your social networks.
* Additional authors can setup their own leenk.me accounts and add them to your website.
* Publish to all author's leenk.me accounts whenever a post is published.
* Customize your Tweet format with the custom tags %TITLE% and %URL%
* Automatically shorten URLs with [Twitter Friendly Links](http://wordpress.org/extend/plugins/twitter-friendly-links/), if it is installed - otherwise leenk.me uses TinyURL.

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

= Where can I find help or make suggestions? =

http://leenk.me/contact

== Changelog ==
= 1.1.1 =
* Removed unnecessary CSS and images.
* Changed leenk.me main settings page to reduce CSS bloat.
* Re-organized the Twitter and Facebook code to be a little more efficient for multi-user setups.
* Increased error checking.

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
3. Enable Twitter and Facebook plugins
4. Update your Twitter settings
5. Update your Facebook settings
6. ReTweet and RePublish row actions
7. Post specific settings for Twitter and Facebook