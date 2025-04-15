=== IP Location Redirection ===

Contributors: sabali33
Donate link: https://wise.com/pay/me/eliasua
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl.html
Tested up to: 6.8
Stable tag: 1.0.0
Tags:  ip-based redirection, polylang-redirection
Requires at least: 5.5
Requires PHP: 8.0

This plugin redirects users to the site language version on a translatable page by Polylang plugin.

== Description ==

### How to set it up ###

– Install this plugin at Dashboard > Plugins > Add New, and search for `ip location redirection`
– Go to Settings > General > IP-based Redirection the add API info for the appropriate geo-location service provider.
– That's all

### Features ###
Redirection happens only when:
- the user locale returned from the external service is not the same as the current page locale.
- the user is not logged in with edit posts capability or higher. This allows admins to be able to edit any post.
- the current page has the user locale. If the user locale is English and the current page doesn’t have an English version, redirection won’t happen.
- user locale returned from the api is not available in the page translations and there is an English version in the page translations, redirection is done to the English version. For example a user from Chinese locale visits a page. The page doesn’t have a Chinese version but has among others English version, redirection is done to the English version


### Supported GEO-Location providers ###

– [IPgeolocation](https://ipgeolocation.io/)
– [Ipinfo](https://ipinfo.io/)
– [ip-api.com](https://ip-api.com/)

### Plugin Privacy Policy
This plugin doesn't save any user personal information. The data return from the geo-location provide is used for the purpose redirecting the user.

