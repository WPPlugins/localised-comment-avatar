=== Localised Comment Avatar ===
Contributors: benbacardi
Donate link: http://www.bencardy.co.uk
Tags: comments, avatars
Requires at least: 2.1.3
Tested up to: 2.3.3
Stable tag: 2

The LCA Plugin allows commenters to upload and display an avatar based on either name, e-mail address, url or a combination of all three entered.

== Description ==

This plugin allows readers to specify a name, e-mail address and/or URL that they will always use when commenting, and to upload and asign an image to this combination of specified values. The plugin allows the blog owner to specify which of the three values are used when checking for combinations in the database, as well as specify where the images are uploaded to and their maximum size when outputted to the page. Owner can specify an image to use if a commenter has not uploaded one, if an image is wanted at all.

== Installation ==

1. Upload the lca.php file into your wp-content/plugins/ folder.
1. Navigate to the Plugins menu within your Wordpress admin area and activate the plugin.
1. Click "Manage" and then "LCA Management" and adjust the settings as necessary.
1. Place the tag `<?php lca_form(); ?>` within your template in the place where you wish the upload form to appear.
1. Place the tag `<?php lca_avatar( $comment ); ?>` within your template in the place where you wish each commenter's avatar to appear. This must be placed within the comment loop on the Comments page.

The upload directory must be writable by the PHP script. The script will warn you if this is not the case and ask you to take action before using the plugin. This usually means setting the permissions to 666 or such like.

**Styling the Form and Avatar**

Both the form and avatar have been given special classes for use within CSS.

**Form Classes:**

* form.lca_form - The form itself.
* input.lca_input - The input fields in the form.
* input.lca_submit - The submit button in the form.
* div.lca_error - The error message div.
* div.lca_thankyou - The thank you message div once the avatar has been uploaded.

**Avatar Classes:**

* img.lca_image - The avatar.

== Frequently Asked Questions ==

= Where can I leave feedback? =

[http://www.sleepingmonkey.co.uk/localised-comment-avatar/](http://www.sleepingmonkey.co.uk/localised-comment-avatar/ "Link")

= How can I style the Avatar and Form? =

See the Installation section.