## [Easy Digital Downloads - Commission Fees](https://sellcomet.com/downloads/commission-fees/)

Commission Fees allows you to charge vendors an additional flat rate amount or percentage fee on each transaction. This allows you to offset payment processing fees, Amazon S3 file delivery costs, or calculate and retain withholding tax amounts for accounting or compliancy purposes.

A fee can be a flat rate amount or percentage-based, calculated inclusively or exclusively, a rate globally set (applied by default to all commission recipients), or on a per-download or per-user basis. Similarly, fees can be disabled for specific products or users.

To understand how the commission fee calculations work, let’s take a look at the following examples:

*Flat Amount Fee*: Vendor sells a £10.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of £5.00. Since we are charging all vendors a flat rate fee of £0.50, the vendor ends up with an adjusted commission amount of £4.50. The final commission amount is calculated by deducting the fee (£0.50) from the base (£5.00) amount.

*Percentage (Exclusive) Fee*: Vendor sells a £20.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of £10.00. Since we are charging all vendors a percentage fee of 10%, the calculated fee would be £1.00 (10 * .1), for which the vendor ends up with an adjusted commission amount of £9.00 (10 – (10 * .1)).

*Percentage (Inclusive) Fee*: Vendor sells a £20.00 product, for which he/she receives 50% of the item price, resulting in a base commission amount of £10.00. Since we are using the inclusive calculation type, the “fee” is subsumed within the base commission amount (£10.00). In this instance we are charging the vendor a 20% fee, which results in £1.67 (10 – (10 / 1.20)). However, because we are charging a fee to the recipient, we adjust the base commission amount to £8.33.

## Features

* Ability to calculate fees either exclusively or inclusively
* Fee rates can be set (and disabled) on a global, per-user, or per-download basis
* Flat rate amount and percentage-based fee types
* Includes two new email tags – {fee} and {fee_amount} which can be included in commission notifications
* Ability to disable automatic fee adjustments and store the calculated amounts for future use
* Seamless integration into the commission’s admin panel, displaying the commission fee amount, type, calculation basis (exclusive or inclusive) and store commission/earnings
* Comprehensive fee report – export a detailed “fees” report containing comprehensive commission and associated fee metadata
* Display fees to vendors – optionally enable “fees” to be displayed on the commission’s shortcodes, so vendors can see how much was charged per transaction
* Translation-ready and contains a POT file to get you started!
* Easily extendable – have a specific use-case? Commission Fees includes plenty of developer-ready filters and action hooks to make it possible!
* Developed using the best practices, with security, extensibility, and readability in mind

This plugin requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/) and [Commissions](https://easydigitaldownloads.com/downloads/commissions/).

## Installation

For detailed setup instructions, visit the official [Documentation](https://sellcomet.com) page.

1. You can clone the GitHub repository: `https://github.com/davidsherlock/edd-commission-fees.git`
2. Or download it directly as a ZIP file: `https://github.com/davidsherlock/edd-commission-fees/archive/master.zip`

This will download the latest developer copy of Commission Fees.

## Bugs
If you find an issue, let us know [here](https://github.com/davidsherlock/edd-commission-fees/issues?state=open)!

## Support
This is a developer's portal for Commission Fees and should _not_ be used for support. Please visit the [support page](https://sellcomet.com/contact/) if you need to submit a support request.

## Contributions
Anyone is welcome to contribute to Commission Fees.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/davidsherlock/edd-commission-fees/issues) on GitHub
2. Send us a Pull Request with your bug fixes and/or new features. Please open an issue beforehand if one does not currently exist.
3. Provide feedback and suggestions on [enhancements](https://github.com/davidsherlock/edd-commission-fees/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open)
