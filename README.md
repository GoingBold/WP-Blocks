# ![WP Blocks logo](https://user-images.githubusercontent.com/17580973/39705883-8f9c6f8a-5207-11e8-8cb6-61cdf619f07d.png)

WP Blocks is a full WYSIWYG content management solution for WordPress. It's built on tried and tested best practices and allows content managers to create phenomenally engaging experiences with absolutely no coding. It takes the best bits of core wp, and builds on them to create a solution fit for sites that demand the very best in content management.

![wpb-intro](https://user-images.githubusercontent.com/17580973/39476840-587c8bf4-4d55-11e8-8d4f-dc540fe1d21b.jpg)

## User guide

*This is version 1.0.0 of the user guide. Lots more images, more beginner friendly language, and more in-depth tutorials are coming soon (along with [Standard Readme formatting](https://github.com/RichardLitt/standard-readme)).*

**If you're a PHP or JavaScript developer then help make WP Blocks even better! Please lend a hand and contribute on Github.**

### Introduction

WP Blocks is a user interface for the post edit screen that provides a full WYSIWYG experience when creating content. It takes what you've created visually and saves it to [the_content()](https://developer.wordpress.org/reference/functions/the_content/ "Info on the_content() on wp.org"), just like the default editor and Gutenberg. It's built on [Advanced Custom Fields](https://www.advancedcustomfields.com/ "Advanced Custom Fields homepage") and is fully responsive.

WP Blocks is easy to develop for compared to other WordPress WYSIWYG solutions. If you know HTML and CSS then you can pick up developing with WP Blocks in less time than it takes to watch a movie.

To demonstrate WP Blocks this plugin has been created, with one block, a hero, with lots of settings.

#### Features of this plugin

* Custom gradient settings, so users can choose from a range of gradients for the hero
* A text field (using the [wysiwyg field type](https://www.advancedcustomfields.com/resources/wysiwyg-editor/ "Info on the WYSIWYG field type on advancedcustomfields.com")
* Ability to add a background image
* Ability to set opacity (and set opacity colour)
* Ability to add a button to the hero

Many blocks you create won't need as many settings as this example, so mastering this will put you in good stead to create lots of different blocks. **The skills you will gain from diving in to this plugin will allow you to create an entire site with WP Blocks**. Everything is fully commented, 'wp-blocks-core-styles.css' for example, comes with comments for pretty much everything, so you'll know what the CSS does without having to reference anything else.

WP Blocks doesn't have to stay as a plugin when you're creating a theme with it, it's only a plugin so you can quickly install it and play around with it. You can integrate it with your theme as everything only relates to the styles, think of it as a framework (like Bootstrap) for WP editing.

### Plugin structure

* wordpress-blocks
    * assets
        * img
            * admin
                * gradients
                    * wpb-gradient-icon-sprite.png
                    * wpb-gradient-icon-sprite.svg
            * gradients
                * b-t
                    * wpb-gradient-vertical-l.png
                    * wpb-gradient-vertical-l.svg
                    * wpb-gradient-vertical-m.png
                    * wpb-gradient-vertical-m.svg
                    * wpb-gradient-vertical-s.png
                    * wpb-gradient-vertical-s.svg
                    * wpb-gradient-vertical-xl.png
                    * wpb-gradient-vertical-xl.svg
                    * wpb-gradient-vertical-xs.png
                    * wpb-gradient-vertical-xs.svg
                    * wpb-gradient-vertical-xxs.png
                    * wpb-gradient-vertical-xxs.svg
                * l-r
                    * wpb-gradient-l-r-l.png
                    * wpb-gradient-l-r-l.svg
                    * wpb-gradient-l-r-m.png
                    * wpb-gradient-l-r-m.svg
                    * wpb-gradient-l-r-s.png
                    * wpb-gradient-l-r-s.svg
                    * wpb-gradient-l-r-xl.png
                    * wpb-gradient-l-r-xl.svg
                    * wpb-gradient-l-r-xs.png
                    * wpb-gradient-l-r-xs.svg
                    * wpb-gradient-l-r-xxs.png
                    * wpb-gradient-l-r-xxs.svg
                * r-l
                    * wpb-gradient-r-l-l.png
                    * wpb-gradient-r-l-l.svg
                    * wpb-gradient-r-l-m.png
                    * wpb-gradient-r-l-m.svg
                    * wpb-gradient-r-l-s.png
                    * wpb-gradient-r-l-s.svg
                    * wpb-gradient-r-l-xl.png
                    * wpb-gradient-r-l-xl.svg
                    * wpb-gradient-r-l-xs.png
                    * wpb-gradient-r-l-xs.svg
                    * wpb-gradient-r-l-xxs.png
                    * wpb-gradient-r-l-xxs.svg
        * js
            * admin
                * wp-blocks-core.js
    * css
        * admin
            * wp-blocks-core-styles.css
        * style.css
    * inc
        * admin
            * hero-post-edit-screen.php
        * hero-front-end.php
    * readme.text
    * wp-blocks.php

### Downloading and installing

The first thing you need to do is install the plugin, you need to have ACF Pro installed, and the plugin should work on any (well developed) theme, but you may want to play around on a fresh install, just to be on the safe side.

Download the folder **wordpress-blocks** from Github, zip it and upload it via the WordPress dashboard as you would any other plugin. Alternatively, download the **wordpress-blocks** folder from Github and upload it via sftp to the plugins folder of your site. Activate it and you're good to go!

### The post edit screen

Head over to the add/edit post (or page) screen and you'll see the WYSIWYG editor appear above the default post editor. Click 'Add Block' and select 'Hero', the hero block placeholder will then appear and the settings panel will appear to the right (it takes up the whole screen on mobile). From here, add your content and adjust settings as necessary and you'll see that as you do it will update in real time, and look exactly as it does on the front-end, although your current theme styles may interfere, remember that WP Blocks is meant for theming, so when building a theme with it, you'll be writing the styles for each block!

If you save the post, the html for the hero will be put in the_content(). If you edit a page that already has content in the_content() then a message appears allowing you to view the default editor.

### Creating your own blocks

So if you've followed the above, you should have a good idea of what you can achieve with WP Blocks. The next steps are to create your own blocks.

* Download the json from Github and head to Custom Fields > Tools > Upload
* Upload the JSON
* You'll see WP Blocks on the fields page
* Add your flexible content. Each flexible content is a block, and you'll see that the hero has 28 sub fields. [Info on creating flexible content here](https://www.advancedcustomfields.com/resources/flexible-content/ "Info on creating flexible content").
* The subfields you add to your flexible content are the settings for the blocks, and appear in the settings panel that displays to the right when you click on a block.

#### CSS management

Effective CSS management is the difference between making it fairly straightforward to copy the styles from the front-end to the back-end, to making it frustrating as heck. When CSS needs to (in effect) be in two places, we use granular css, which breaks down css in to sections. [You can read about it here](https://bit.ly/granularcss "Info on granular css").

#### Getting things displaying on the front-end and back-end

This is done via PHP, and most of it consists of conditionals, *if this then that, otherwise do this* etc. There's no complex syntax involved, and no maths (well, not unless you want to). Read this article: https://www.advancedcustomfields.com/resources/flexible-content/ and this one: https://www.advancedcustomfields.com/resources/acf-fields-flexible_content-layout_title/ and then take a look at the files 'hero-front-end.php' and 'hero-post-edit-screen.php' to see how the PHP is implemented. 'hero-front-end.php' and 'hero-post-edit-screen.php' contain the same stuff, just 'hero-front-end.php' is echoed and 'hero-post-edit-screen.php' returns a string. You could change 'hero-front-end.php' to return a string as well if you wanted, which would probably make managing the two even easier, but echoing is faster (not that it would make that much difference as we're saving to the_content() anyway).

## Shameless plug

If you're wanting to see how a theme uses WP Blocks, check out [Campaign Pro](https://bit.ly/campaignp "Campaign Pro homepage") - it comes with 15 content blocks, a WYSIWYG header and footer builder, and a WYSIWYG colour settings page, all built using WP Blocks.

**_If you spot a bug, or you get stuck, open an issue._**
