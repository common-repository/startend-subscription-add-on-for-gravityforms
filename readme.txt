===  STARTEND Subscription Add-On for GravityForms ===
Contributors: ZeroZen Design, cncrrnt
Author: cncrrnt
Tags: Gravity Forms, Addon, Stripe, Gravity Forms Stripe, Date, Cancel Subscription
Requires PHP at least: 5.2.4
License: GPLv2 or later
Tested up to: 6.3
Stable tag: 4.1.6
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Description: STARTEND is a Gravity Forms Add-on that allows you to set one or many future start dates and customize an automated end date for your Gravity Forms Stripe Subscriptions.

== Description ==

STARTEND is a Gravity Forms Add-on that allows you to set one or many future start dates and customize an automated end date for your Gravity Forms Stripe Subscriptions.

→ Combine with GravityStripe for a full-fledged Subscription Management Solution! ←


== BASIC VERSION ==
1. Set a single or multiple future start dates
2. Set a single or multiple fixed end dates


== PREMIUM VERSION ==
1. Set a single or multiple future start dates
2. Set a single or multiple fixed end dates
3. End a subscription after a specified number of payments
4. End a subscription after a specified term limit


== Examples ==
1) Summer camp has 3 different start dates. The camp charges on the first day of camp. 1 week later the second and final automated payment comes out.
Solution
3 drop-down dates for parents to select from. The billing cycle is set to weekly. The subscription ends after 2 payments.


2) A purchaser has the option to pay for a product in full or in 6 month installments.
Solution
Pay in full charges the user using a product feed in Stripe. The installment option triggers the conditional subscription feed. Only the end date setting is used. The billing cycle is set to monthly. The term limit option is used and set to 6 months.


3) A service charges a user every month for one year. If the service subscription isn't renewed, it cancels automatically.
Solution
Only the end date setting is used. The billing cycle is set to annual. The “x number of payments” option is used and set to 1.


STARTEND is the only way to fully customize the start and end date for Stripe subscriptions on Gravity forms.
Download the Basic Version or BUY THE PRO VERSION Today!
Basic Version comes with limited forum support, Pro Version comes with priority ticket support.

Want to give subscribers the ability to manage their subscriptions on their own? Check out [https://gravitystripe.com]https://gravitystripe.com GravityStripe! With STARTEND and GravityStripe you can save hundreds of dollars per year and turn your website into a full-fledged subscription management platform that YOU control.


== Changelog ==
4.1.6
- Added support for php 8.0
- Added couple of hooks to work with Start Date. 
- Made compatible to work with Gravityforms Stripe plugin. 
- Enhanced flow for faster processing.

4.0.4
- Added support for start date to override trials or not. By default if trial is set, Start date will not perform any operations. 
- Added "gs_startend_override_trial" filter to apply start date if trial is set
- Setup fee to be excluded in number of payments cancellation count. 

== Video ==
[youtube https://youtu.be/0XOCdS21XdU]


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress