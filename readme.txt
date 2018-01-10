=== Commission Fees ===
Contributors: Sell Comet
Donate link: https://sellcomet.com/downloads/commission-fees/
Tags: Add-On, Commissions, Easy Digital Downloads, EDD, Extension, Fees, Marketplace, Plugin, Vendors, WordPress
Requires at least: 4.0
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Charge commission recipients an additional flat rate amount or percentage fee on each transaction.

== Description ==

Commission Fees allows you to charge Easy Digital Downloads Commissions recipients (commonly referred to as “vendors”) an additional flat rate amount or percentage fee on each transaction. This allows you to offset payment processing fees, Amazon S3 file delivery costs, or calculate and retain withholding tax amounts for accounting or compliancy purposes.

A fee can be a flat rate amount or percentage-based, calculated inclusively or exclusively, a rate globally set (applied by default to all commission recipients), or on a per-download or per-user basis. Similarly, fees can be disabled for specific products or users.

To understand how the commission fee calculations work, let’s take a look at the following examples:

**Flat Amount Fee**: Vendor sells a £10.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of £5.00. Since we are charging all vendors a flat rate fee of £0.50, the vendor ends up with an adjusted commission amount of £4.50. The final commission amount is calculated by deducting the fee (£0.50) from the base (£5.00) amount.

**Percentage (Exclusive) Fee**: Vendor sells a £20.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of £10.00. Since we are charging all vendors a percentage fee of 10%, the calculated fee would be £1.00 (10 * .1), for which the vendor ends up with an adjusted commission amount of £9.00 (10 – (10 * .1)).

**Percentage (Inclusive) Fee**: Vendor sells a £20.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of £10.00. Since we are using the inclusive calculation type, the “fee” is subsumed within the base commission amount (£10.00). In this instance we are charging the vendor a 20% fee, which results in £1.67 (10 – (10 / 1.20)). However, because we are charging a fee to the recipient, we adjust the base commission amount to £8.33.

**Features**

* Ability to calculate fees either exclusively or inclusively
* Fee rates can be set (and disabled) on a global, per-user, or per-download basis
* Flat rate amount and percentage-based fee types
* Includes two email tags – {fee} and {fee_amount} which can be included in commission notifications
* Ability to disable automatic fee adjustments and store the calculated amounts for future use
* Seamless integration into the commission’s admin panel, displaying the commission fee amount, type, calculation basis (exclusive or inclusive) and store commission/earnings
* Comprehensive fee report – export a detailed “fees” report containing comprehensive commission and associated fee metadata
* Display fees to vendors – optionally enable “fees” to be displayed on the commission’s shortcodes, so vendors can see how much was charged per transaction
* Commission Fees is translation-ready and contains a British English (UK) POT file to get you started!
* Easily extendable – have a specific use-case? Commission Fees includes plenty of developer-ready filters and action hooks to make it possible!
* Developed using the best practices, with security, extensibility, and readability in mind

> Notice: This extension requires Easy Digital Downloads (free) and Commissions (minimum version of 3.4 to function) (paid). Refunds will not be granted to customers who do not meet this criteria.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/edd-commission-fees` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)

== Changelog ==

Version 1.0.0, November 19, 2017

Initial Release

== Upgrade Notice ==
