# Ask AI - OneTag for WordPress

A WordPress plugin that adds an Ask AI box to your site. A visitor types a question and gets a short answer written from your content, with links to the pages it used. It loads the hosted [OneTag AI](https://onetagai.com/?ref=wordpress) widget with one script tag in the footer. Nothing runs on your server.

Slug on wordpress.org: `ask-ai-onetag`.

## Features

- Settings > Ask AI: site key, button label, position, color, language, and an on/off switch.
- One `async` script tag in the footer, added with `wp_enqueue_script`. No database tables, no server requests.
- Nothing loads on the public site until you add a key. Until then the dashboard shows a notice with the signup link.
- Answers come from the current page, and from your whole site when it has an `llms-full.txt` at the root (several llms.txt plugins write one) or a Pagefind index at `/pagefind/`.
- Deleting the plugin removes its one option.

## Install

From a zip:

1. Download `dist/ask-ai-onetag.zip` from this repository.
2. In WordPress, go to Plugins > Add New > Upload Plugin and upload it.
3. Activate it.

From source: copy the `ask-ai-onetag/` folder into `wp-content/plugins/`.

## Before you go live: get a free key

Get a key on [onetagai.com](https://onetagai.com/?ref=wordpress#get-key), or from a terminal:

```sh
curl -X POST https://onetagai.com/api/signup -H "Content-Type: application/json" -d '{"email":"you@example.com"}'
```

Paste the key in Settings > Ask AI and save.

The key is public by design. It ends up in your page source, like an analytics ID. The free plan has 1,000,000 tokens a month with a hard cap. No bill can appear.

## Settings

| Setting | Script attribute | Values |
| --- | --- | --- |
| Show the Ask AI box | (none) | On loads the script, off loads nothing |
| Site key | `data-key` | Your `ot_...` key |
| Button label | `data-label` | Text, up to 40 characters. Default "Ask AI" |
| Position | `data-position` | Automatic, bottom right, bottom left, or no button |
| Color | `data-color` | Hex without `#`, for example `2563eb` |
| Language | `data-lang` | Same as the page, `en`, `zh-CN`, `zh-TW` |

`data-ref="wordpress"` is always set.

With "No button" you open the box from your own link or button with `onetagAskDocs.open()`.

## How it works

On `wp_enqueue_scripts` the plugin enqueues `https://onetagai.com/ask-docs.js` in the footer with the `async` strategy (WordPress 6.3 and later). A `script_loader_tag` filter adds the `data-*` attributes with `WP_HTML_Tag_Processor`, which escapes every value. The rendered tag looks like this:

```html
<script data-key="ot_..." data-ref="wordpress" src="https://onetagai.com/ask-docs.js" id="ask-ai-onetag-js" async data-wp-strategy="async"></script>
```

Settings use the Settings API, so the form carries a nonce and every field is sanitized on save.

## What is sent to OneTag AI

The plugin sends nothing from your server. In the visitor's browser, the widget script is downloaded from onetagai.com, and when the visitor asks a question the question, your site key, the page address and the matching text from your site go to `https://onetagai.com/v1/ask`. See the `== External services ==` section of `ask-ai-onetag/readme.txt`, the [terms](https://onetagai.com/terms?ref=wordpress) and the [privacy policy](https://onetagai.com/privacy?ref=wordpress).

## Without a key

The plugin loads nothing on the public site and shows an admin notice with the signup link. The demo key `ot_demo000000000000000000000` only answers on `localhost`, so it is not used as a fallback.

## Development

- `bin/build-zip.sh` builds `dist/ask-ai-onetag.zip`, the file to upload to wordpress.org.
- Requires WordPress 6.3 or later and PHP 7.4 or later.

## License

GPLv2 or later. See `LICENSE`.
