=== Ask AI - OneTag ===
Contributors: onetagai
Tags: ai, chatbot, search, ai-chatbot, faq
Requires at least: 6.3
Tested up to: 7.1
Stable tag: 0.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds an Ask AI box that answers visitor questions from your own pages. Paste a free site key and it works. No account setup inside WordPress.

== Description ==

Ask AI adds a small "Ask AI" box to your site. A visitor types a question. The box reads your content and writes a short answer with links to the pages it used.

What it reads: the page the visitor is on, and your whole site when an `llms-full.txt` file exists at the site root (several llms.txt plugins write one). It also picks up a Pagefind index at `/pagefind/` if you have one.

It uses the hosted OneTag AI widget. The plugin adds one script tag to the footer of your public pages. Nothing runs on your server and no tables are added to your database.

Features:

* One setting that matters: your site key.
* Answers come from your own content, with links back to it.
* Button label, position, color and language can be changed in Settings > Ask AI.
* Turn it off with one checkbox without losing your settings.
* Loads asynchronously in the footer, so it does not slow down your page.
* Nothing is loaded on the public site until you add a key.

The free plan includes 1,000,000 tokens, one time, no monthly reset with a hard cap. No bill can appear.

== Installation ==

1. Install the plugin from Plugins > Add New, or upload the zip in Plugins > Add New > Upload Plugin.
2. Activate it.
3. Get a free site key at [onetagai.com](https://onetagai.com/?ref=wordpress#get-key).
4. Go to Settings > Ask AI, paste the key and save.
5. Open your site. The Ask AI button is in the bottom corner, or next to your search box.

== Frequently Asked Questions ==

= Is the site key a secret? =

No. It is public by design, like an analytics ID. It ends up in your page source.

= What does it cost? =

The free plan has 1,000,000 tokens, one time, no monthly reset with a hard cap. There is no card on file, so no bill can appear.

= Where does the button appear? =

With the "Automatic" position it sits next to your theme's search box when it finds one, else it floats in the bottom right corner. You can also pick bottom left, or "No button" and open the box from your own link with `onetagAskDocs.open()`.

= Can I use it in Chinese? =

Yes. The interface follows the language of the page. You can force English, Simplified Chinese or Traditional Chinese in the settings.

= Does it work without a key? =

The plugin loads nothing on your public site until you add a key. The settings page shows a notice with the link to get one.

== External services ==

This plugin connects to the OneTag AI service (https://onetagai.com/?ref=wordpress) to show the Ask AI box and to write its answers.

* When a page of your public site loads and a site key is set, the visitor's browser downloads the widget script from https://onetagai.com/ask-docs.js. This request contains the usual browser data (IP address, user agent, the page address as referrer).
* When a visitor asks a question, the browser sends the question, your site key, the address of the current page, and the matching text from the current page or from your site's public `llms-full.txt` or Pagefind index to https://onetagai.com/v1/ask. The answer is sent back to the browser.
* The plugin itself does not send anything from your WordPress server. It does not send the content of your admin area, your users or your visitors' accounts.
* Nothing is loaded or sent while the site key field is empty or the "Show the Ask AI box" option is off.

This service is provided by OneTag AI: [Terms of service](https://onetagai.com/terms?ref=wordpress), [Privacy policy](https://onetagai.com/privacy?ref=wordpress).

== Screenshots ==

1. The Ask AI box answering a question with links to the site's pages.
2. Settings > Ask AI.

== Changelog ==

= 0.1.0 =
* First release: site key, label, position, color, language and on/off settings.

== Upgrade Notice ==

= 0.1.0 =
First release.
