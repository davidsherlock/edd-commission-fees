=== Easy Digital Downloads - Commission Fees ===
Contributors: sellcomet, mandyjonesmusic
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LLR26ABB7C39Q
Tags: easy digital downloads, digital, download, downloads, edd, sellcomet, commissions, fees, ecommerce, e-store, eshop, marketplace, vendors, transaction, paypal
Requires at least: 4.0
Tested up to: 4.9.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Charge vendors an additional flat amount or percentage fee on each transaction.

== Description ==

**Commission Fees** for Easy Digital Downloads allows you to charge vendors an additional flat amount or percentage fee on each transaction. This allows you to offset payment processing fees, file delivery costs, as well as calculate and retain withholding tax amounts for accounting or compliancy purposes.

A fee can be either a flat rate amount or percentage-based, with the type set on a per-download basis, and rate set either globally (applied by default to all commission recipients), or overridden on a per-download or per-user basis. This allows for flexible fee structures and streamlined, granular control over your commissions.

== Use-case Examples ==

Let’s look at some real-world examples of how Commission Fees could potentially be utilised in different scenarios:

* All vendors receive 70% for each product sold on your store. To offset PayPal fees, you charge all vendors an additional $0.25 to help absorb payment processing fees.
* Some products contain large files. For these products you need to charge a higher percentage fee of 2.5% to cover Amazon S3 file delivery costs.
* One product has multiple vendors receiving commissions and you want to set their commission fees independently.

== Understanding Fees ==

To understand how the commission fee calculations work, let’s take a look at the following examples:

**Flat Rate Amount**: Vendor sells a $10.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of $5.00. Since we are charging all vendors a flat rate fee of $0.25, the vendor ends up with an adjusted commission amount of $4.75. The final commission amount is calculated by deducting the fee ($0.25) from the base amount ($5.00).

**Percentage Fee**: Vendor sells a $20.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of $10.00. Since we are charging all vendors a percentage fee of 10%, the calculated fee would be $1.00 (10 * .1), which means the vendor ends up with an adjusted commission amount of $9.00 (10 – (10 * .1)).

== Features ==

* Set commission fees independently on a per download, per user, or global basis
* Easily disable fees for specific users or downloads
* Set different fees for vendors receiving commissions on the same product
* Set a fallback, or default global fee (such as $0.25) that applies to all products by default
* View a comprehensive fee report graph showing fees collected for any given date range
* Ability to revoke fees for specific commission records, or in bulk
* Ability to recalculate commission fees, even for commissions which previously had no fee applied
* Includes two new email tags – `{fee}` and `{fee_rate}` which can be included in commission notification emails
* Includes a new `[edd_commission_fees]` short code allowing your vendors to view the total unpaid, paid, and revoked fees
* Records a detailed note within the payment record when a fee has been charged
* Works perfectly with Simple Shipping, AffiliateWP and Recurring Subscriptions
* Export a detailed report containing comprehensive commission and associated fee meta data
* Seamless integration with Easy Digital Downloads and Commissions
* Translation-ready and contains a POT file to help get you started translating into your own native language
* Have a specific use-case? Commission Fees includes plenty of developer-ready filters and action hooks to make it possible!
* Developed using the best practices, with security, extensibility, and readability in mind

This plugin requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/ "Easy Digital Downloads") and [Commissions](https://easydigitaldownloads.com/downloads/commissions/ "Commissions").

== Bugs ==

If you find an issue, let us know [here](https://github.com/davidsherlock/edd-commission-fees/issues?state=open)!

== Support ==

Please visit the [support page](https://wordpress.org/support/plugin/edd-commission-fees) if you need to submit a support request.

== Contributions ==

Anyone is welcome to contribute to Commission Fees.

There are various ways you can contribute:

* Raise an [Issue](https://github.com/davidsherlock/edd-commission-fees/issues) on GitHub
* Send us a Pull Request with your bug fixes and/or new features. Please open an issue beforehand if one does not currently exist.
* Provide feedback and suggestions on [enhancements](https://github.com/davidsherlock/edd-commission-fees/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open)

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin
1. That's it!

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Frequently Asked Questions ==

== Screenshots ==

== Upgrade Notice ==

== Changelog ==

= 1.0.1, January 30, 2018 =
* Renamed short code from [edd_commission_fees_overview] to [edd_commission_fees] for ease of use
* Improved filter usage and general code base improvements
* Added support for Recurring Subscriptions

= 1.0.0, January 29, 2018 =
* Initial release
