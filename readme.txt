=== Contact Form 7 BWP reCAPTCHA Extension ===
Contributors: manfer
Donate link: http://manfersite.tk/cf7bwpcapt
Tags: Contact Form 7, Contact, Contact Form, CAPTCHA, reCAPTCHA, BWP reCAPTCHA
Requires at least: 2.9
Tested up to: 3.1.3
Stable tag: 0.8

This plugin provides a new tag for the Contact Form 7 Plugin. It allows the usage of a reCAPTCHA field provided by the BWP reCAPTCHA Plugin.

== Description ==

Contact Form 7 is an excellent WordPress plugin but its captcha functionality is limited to a simple captcha.

CF7 BWP reCAPTCHA Plugin adds reCAPTCHA capabilities to contact form 7.

This plugin is the result of the study on how CF7 API, WP APIs and BWP Captcha plugin works, in order to code the necessary hooks to add recaptcha to CF7, including a settings page to configure the theme and language you want to use.

= Requirements =

* You need the [Contact Form 7](http://wordpress.org/extend/plugins/contact-form-7/ "Contact Form 7 Plugin") plugin to be installed and activated.
* You need the [BWP reCAPTCHA](http://wordpress.org/extend/plugins/bwp-recaptcha/ "Better Wordpress reCAPTCHA Plugin") plugin to be installed and activated.

= Settings = 

The settings of the BWP reCAPTCHA plugin are used by default. You can change that behaviour on the settings page of the plugin under:

*BWP reCAPT* -> *CF7 Options*

= Feedback =

If you like the plugin **please rate** it. If you don't like it, **please contact us** so we can address the problem or feature request.

Please if you find the plugin is not working for you and you report it, fill the form with what exactly do you think is not working. Thanks.

This plugin is provided as is by manfer (http://manfersite.tk).

== Installation ==
1. Make a backup of your current installation
1. Make sure you fit the requirements
1. Download and unpack the download package
1. Upload the `contact-form-7-bwp-recaptcha-extension` folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You will now have a "reCAPTCHA" tag option in the Contact Form 7 tag generator

= Usage =
1. On the actual Contact Form 7 configuration page, next to the "Form Box" with code in it, use the drop down click on Generate Tag
1. Choose *reCAPTCHA* (**not** CAPTCHA) 
1. copy the code that it gives you
1. past it into the "Form Box" where the existing code is.

IMPORTANT: The reCAPTCHA is not shown to qualified visitors so you must logout if you want to test the form in which you include the reCAPTCHA.

If you like the plugin **please rate** it. If you don't like it, **please contact us** so we can address the problem or feature request.


== Screenshots ==

1. The new reCAPTCHA option.
2. The property form for the reCAPTCHA field.
3. The reCAPTCHA tag in the form editor.
4. The reCAPTCHA in the finished form.
5. The configuration page under *BWP reCAPT* -> *CF7 Options* 


== Changelog ==

= 0.8 (20120612) =
* Requirements checking code reviewed.

= 0.7 (20120611) =
* Fixed notice because menu_slug undefined.
* Fixed bug after upgrade to CF7 3.2.

= 0.6 (20120208) =
* Fixed for multisite wordpress.

= 0.5 (20120107) =
* Fixed breaks BWP Recaptcha registration form feature.

= 0.4 (20120107) =
* Fixed hide for qualified users feature.

= 0.3 (20120106) =
* Added support for custom theme.
* Better integration with BWP Recaptcha.

= 0.2 (20111218) =
* Updated language file.

= 0.1 (20111217) =
* Total rewrite to stop using some code I don't want to use anymore.

= 0.0.4 (20111130) =
* FIX: Bug on register_scripts.

= 0.0.3 (20111127) =
* FIX: Tag generator not working.

= 0.0.2 (20111117) =
* FIX: Coding standards reviewed.
* FIX: Minor bugs.

= Known Issues = 



== Upgrade Notice ==


