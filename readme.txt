=== leenk.me ===
Contributors: layotte
Tags: twitter, facebook, oauth, profile, pages, social networking, social media, posts, twitter post, tinyurl, twitter friendly links, multiple authors, exclude post, category, categories, retweet, republish, javascript, ajax, connect, status update, leenk.me, leenk, leenk me
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 1.0.2

The leenk.me plugin empowers you to automatically publish to your Twitter and Facebook accounts whenever you publish a new post in WordPress.

== Description ==

leenk.me automatically publishes a tweet to your Twitter account and a status update to your Facebook profile and/or page whenever you publish a new post in your WordPress website. It has the ability to support multiple WordPress authors and allows administrators to setup the plugin to publish to leenk.me for all authors, whenever anyone publishes a post.

With leenk.me you can...

* choose which categories are published automatically
* exclude individual posts from being tweeted to Twitter or Published to Facebook
* ReTweet and RePublish already published posts
* choose to publish all authors
* customize your Tweet format with the custom tags %TITLE% and %URL%

Currently leenk.me supports two URL shortening services for its Twitter plugin. TinyURL is the default shortener, the leenk.me plugin will attempt to use the post's URL shortened by TinyURL. If it is unable to, it will use the regular site URL. The recommended shortener is a WordPress plugin called [Twitter Friendly Links](http://wordpress.org/extend/plugins/twitter-friendly-links/). If Twitter Friendly Links is installed and activated on your WordPress website then leenk.me will use it as the default shortener. Twitter Friendly Links drives traffic directly to your website and does not rely on a second party!

[Support](http://leenk.me/contact)

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `leenk.me` directory to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create a leenk.me account [here](http://leenk.me) to get a leenk.me API and to set up your social networking options.
1. Update the leenk.me plugin options with your leenk.me API Key and set any plugin options. 
1. Next time you publish a new post it will auotomatically be sent to leenk.me and distributed to your social networks.

== Frequently Asked Questions ==

= Where can I find help or make suggestions? =

http://leenk.me/contact

== Changelog ==
= 1.0.2 =
Fixed bug caused by not having thumbnails enabled in WP2.8.x and WP2.9.x.

= 1.0.1 =
Fixed small bug: removed default check from Facebook profile for new plugin activations.

= 1.0.0 = 
leenk.me is a fork from [Twitter Post](http://wordpress.org/extend/plugins/rf-twitterpost/), which unfortunately is no longer supported because Twitter deactivated their REST API.

== Screenshots ==

1. leenk.me plugin settings page
2. verify your leenk.me API key
3. Enable Twitter and Facebook plugins
4. Update your Twitter settings
5. Update your Facebook settings
6. ReTweet and RePublish row actions
7. Post specific settings for Twitter and Facebook