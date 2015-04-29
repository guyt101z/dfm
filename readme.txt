=== Dynamic Form Maker ===
Contributors: jakir
Donate link: 
Tags: form, forms, contact form, contact forms, form, forms, form to email, email form, email, input, validation, jquery, shortcode, form builder, contact form builder, form manager, form creator
Requires at least: 3.5
Tested up to: 4.0.1
Stable tag: 1.0.0
License: GPLv2 or later

Build beautiful, fully functional contact forms in only a few minutes without writing PHP, CSS, or HTML.

== Description ==

*Dynamic Form Maker* is a plugin that allows you to build and manage all kinds of forms for your website in a single place.  Building a fully functional contact form takes only a few minutes and you don't have to write one bit of PHP, CSS, or HTML!

= Upgrade to Dynamic Form Maker Pro =

= Features =

* Add fields with one click
* Drag-and-drop reordering
* Simple, yet effective, logic-based anti-SPAM system
* Automatically stores form records in your WordPress database
* Manage form records in the WordPress dashboard
* Export records to a CSV file
* Send form submissions to multiple emails
* jQuery Form Validation
* Customized Confirmation Messages
* Redirect to a WordPress Page or a URL
* Confirmation Email Receipt to User
* Standard Fields
* Required Fields
* Shortcode works on any Post or Page
* Embed Multiple Forms on a Post/Page
* One-click form duplication. Copy a form you've already built to save time
* Use your own CSS (if you want)
* Multiple field layout options. Arrange your fields in two, three, or a mixture of columns.

= Field Types =

* Fieldset
* Section (group fields within a fieldset)
* Text input (single line)
* Textarea (multiple lines)
* Checkbox
* Radio (multiple choice)
* Select dropdown
* Address (street, city, state, zip, country)
* Date (uses jQuery UI Date Picker)
* Email
* URL
* Currency
* Number
* Time (12 or 24 hour format)
* Phone (US and International formats)
* HTML
* File Upload
* Instructions (plain or HTML-formatted text)

= Form Records =

* Manage submitted records in WordPress dashboard
* Bulk Export to CSV
* Bulk Delete
* Advanced Filtering
* Search across all records
* Collect submitted data as well as date submitted and IP Address

= Customized Confirmation Messages =

* Control what is displayed after a user submits a form
* Display HTML-formatted text
* Redirect to a WordPress Page
* Redirect to a custom URL

= Notification Emails =

* Send a customized email to the user after a user submits a form
* Additional HTML-formatted text to be included in the body of the email
* Automatically include a copy of the user's entry

= SPAM Protection =

* Automatically included on every form
* Uses a simple and accessible, yet effective, [text CAPTCHA](http://textcaptcha.com/) verification system

== Installation ==

1. Go to Plugins > Add New
1. Click the Upload link
1. Click Browse and locate the `dynamic-form-maker.x.x.zip` file
1. Click Install Now
1. After WordPress installs, click on the Activate Plugin link

== Frequently Asked Questions ==

= How do I create a form? =

1. Click on the Dynamic Form Maker > Add New link and enter a few form details
1. Click the form fields from the box on the left to add it to your form.
1. Edit the information for each form field by clicking on the down arrow.
1. Drag and drop the elements to sort them.
1. Click Save Form to save your changes.

= Can I use my own verification system such as a CAPTCHA? =

Dynamic Form Maker uses a [text CAPTCHA](http://textcaptcha.com/). If you decide to upgrade to Dynamic Form Maker Pro, you will gain [Akismet](https://akismet.com/) support.

= Emails are not being sent =

*Note*: Form submissions will always be saved in the database whether or not the email was sent.

**Check SPAM folder**

A quick look in the SPAM folder will tell you if the emails are being routed into the folder. If so, simply train your email client to not treat those emails as SPAM

**Configure your site to use SMTP**

Some people have reported that after the form is submitted, no email is received. If this is the case for you, it typically means that your server or web host has not properly configured their SMTP settings.

Try using a plugin such as [WP Mail SMTP](http://wordpress.org/extend/plugins/wp-mail-smtp/) to correct the issue.

**Set the Reply-To email to a same domain email**

Setting up SMTP will get you part of the way there. For most, it solves the problem. For others, it requires additional configuration

If you find that emails are not being sent, you should first confirm that you have completed all of the details in the `Form Settings > Email section`. Next, be sure to set the Reply-To option to an email that exists on the same domain as your WordPress site.

**Set the Sender email to an email that exists on the domain**

In addition to the Reply-To header, some hosts require that the Sender header is also set to an email that exists on the domain.  By default, the Sender email is automatically set to either your admin email if the domain matches.  If it does not match, then a local email address is created (wordpress@yoursitename.com).

To change this behavior to use an email that exists on the domain, you will need to set the Sender Mail Header option on the `Dynamic Form Maker > Settings` page.

**Possible mod_security conflict**

Some servers are overzealous in their restrictions on the $_POST object and will block anything with certain keywords. Check your server logs and look for any 403 Forbidden or 500 Internal Server errors. If you notice these errors when submitting a form, contact your host and find out if there are any restrictions.

**Enable local mail for your domain**

Be sure to enable local mail delivery for your domain. Disabling local mail delivery is common if you are using an external mail server, but can cause bounce-backs saying the email user does not exist.

Also, if possible, check your server’s email logs or have your host check them for you and see if it’s refusing to send an email. It’s possible your email server is attempting to send the emails but can’t for missing mail resources, security, SPAM filtering, or other technical problems.

= Resolving Theme or Plugin Conflicts =

Dynamic Form Maker is built using preferred WordPress coding standards. In many cases, some theme authors or plugin developers do not follow these standards and it causes conflicts with those that do follow the standards. The two most common issues have to do with either jQuery or CSS.

**jQuery conflicts**

Dynamic Form Maker requires at least jQuery version 1.7. Please make sure your theme is updated to use the latest version of jQuery.

**CSS conflicts**

**Theme conflicts**

If you have confirmed that you are using the latest version of jQuery and can rule out CSS conflicts, there's probably something in your theme still causing problems.

1. Activate the default Twenty Eleven theme
1. Test your site to see if the issue still occurs

Still having problems even with Twenty Eleven running? If not, it's a conflict with your theme. Otherwise, it's probably a plugin conflict.

**Plugin conflicts**

Before following this process, make sure you have updated all plugins to their latest version (yes, even Dynamic Form Maker).

1. Deactivate ALL plugins
1. Activate Dynamic Form Maker
1. Test your site to see if the issue still occurs

If everything works with only Dynamic Form Maker activated, you have a plugin conflict. Re-activate the plugins one by one until you find the problematic plugin(s).

= Customizing the form design =

= Customizing the Date Picker =

The jQuery UI Date Picker is a complex and highly configurable plugin. By default, Dynamic Form Maker's date field will use the default options and configuration.

To use the more complex features of the Date Picker plugin, please read these tutorials from the blog:

= How do I translate the error messages to my language? =

The validation messages (ex: ‘This field is required’ or ‘Please enter a valid email address’) are generated by the jQuery Form Validation plugin.

By default, these messages are in English. To translate them, you can either use the free add-on Custom Validation Messages or follow the manual JavaScript method.

Follow these instructions:

In your theme folder, create a JavaScript file. In this example, I'm using `myjs.js`. Add the following code to it and customize the language to what you need:

`jQuery(document).ready(function($) {
    $.extend($.validator.messages, {
        required: "Eingabe nötig",
        email: "Bitte eine gültige E-Mail-Adresse eingeben"
    });
});`

Now, in your functions.php file, add the following piece of code:

`add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
function my_scripts_method() {
   wp_register_script( 'my-dfm-validation',
       get_template_directory_uri() . '/js/my-js.js',
       array( 'jquery', 'jquery-form-validation' ),
       '1.0',
       false );

   wp_enqueue_script( 'my-dfm-validation' );
}`

== Screenshots ==

1. Dynamic Form Maker page
2. Configuring field item options
3. Form Records management screen
4. Rendered form on a page