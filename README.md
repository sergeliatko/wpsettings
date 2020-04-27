# WPSettings
## WordPress Settings API Framework
This PHP package helps you to speed up the development of your settings screens in WordPress admin. The framework allows you to easily add options, settings sections and admin pages to WordPress admin, taking care to register, sanitize, properly add and display your settings accordingly WordPress Settings API and best coding standards.

## Who it is useful for
WPSettings farmework is intended to help plugin and theme developers who need to quickly create consistent admin interfaces in WordPress no matter how small or big their projects are. It is especially usefull for developers who use WordPress as a "spare wheel" and are not very experienced with WordPress specifics and proprietary logic.

Certainly, many may say it is an overkill to use a framework that helps your interact with WordPress Settings API, plus it is not secure to rely on the code you do not control internally...

Well, after more than 10 years being DEEP in WordPress admin interfaces that always looked like native to WordPress - **I ended up coding this framework for me first of all**. It is something I use in all of my projects, simply because it saves me tons of time and head ache.

As for the second part: the library is open source, you may always fork it on GitHub, customize to your guize and evenually make a pull request to benefit the others. The code will stay public available to all of us. And if you want to [suggest features](https://github.com/sergeliatko/wpsettings/issues) or [take part in feature votes or simply support the project](https://www.patreon.com/sergeliatko), you're always welcome.

## How it works
### WPSettings Framework
With WPSettings Framework adding a simple text field to General Settings in WordPress admin and all the code that sanitizes the option value in the database can be reduced to following:

+ one line to load the library in your project main file:
```php
require_once('path_to_wpsettings_folder/autoload.php');
```

+ and the actual snippet:

```php
//make use of the class once in your file
use \SergeLiatko\WPSettings\Setting;
//then create setting like this
$my_option = new Setting( array(
	'option' => 'option_name_in_db',
	'label'  => __( 'My option label', 'my-text-domain' )
) );
```

...and that's it. Seriously, that's all the code needed to make it happen.

### WordPress Settings API
Now count the lines of PHP necessary to get the same (cleanly registered and properly sanitized) result for the same option using the standard WordPress Settings API:

```php
add_action( 'admin_init', function () {
	register_setting(
		'general',
		'option_name_in_db',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
}, 10, 0 );

add_action( 'admin_menu', function () {
	add_settings_field(
		'option-name-in-db',
		__( 'My option label', 'my-text-domain' ),
		function() {
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="%5$s">',
				'text',
				'option-name-in-db',
				'option_name_in_db',
				esc_attr( get_option( 'option_name_in_db', '' ) ),
				'regular-text code'
			);
		},
		'general',
		'default',
		array(
			'label_for' => 'option-name-in-db'
		)
	);
}, 10, 0 );
```

**6 lines** with WPSettings framework vs **30 lines** using WordPress Settings API... sure if you add more options - not all of them will take 30 lines, but still it is 5 times faster in code and skips you hell of a job to learn the WordPress Settings API hiden stones.

## The best part
WPSettings Framework takes care of:

* Adding single or multiple admin pages and/or submenu pages as well as their introductory texts to WordPress admin area
* Adding single or multiple settings sections to existing or your custom admin pages
* Adding setting fields to existing or your custom settings sections:
  * text inputs (all common types: hidden, text, url, email, password, tel, number, range, date etc.)
  * checkboxes (single and multiples) and radio buttons (again single and multiple)
  * textareas
  * dropdowns (allows option groups as well)
  * (WordPress text editors, pages and taxonomy terms dropdowns and colorpickers are coming in the nearest future)
  * any custom coded field you want (you will be surprized how flexible the framework is)
* Adding descriptions to your custom settings sections and help messages to your setting fields
* Registration of your options in WordPress database
* Sanitizing the user data input for most of the option types (you may use your own sanitize functions when needed)
* Proper handling of the option default values - does not save the defaults in database, makes sure it is returned when no value is provided, allows forcing the default value if user missed the value input (very usefull for text options)

And it allows you to rewrite any functionality of the main classes providing your own extensions.

## Installation

### Using composer
Install the latest version of the framework with:

```bash
$ composer require sergeliatko/wpsettings
```

### Using git
Install the latest verions of the framework with:

```bash
git clone https://github.com/sergeliatko/wpsettings wpsettings
```

Install all the required libraries:

```bash
git clone https://github.com/sergeliatko/form-fields form-fields
```
```bash
git clone https://github.com/sergeliatko/html html
```

#### As a submodule
Git submodules are a powerful tool, which allows you to easily include a third-party project of your own while still treating them as two separate projects. Rather than provide an in-depth explanation of the benefits and use of submodules, it's recommended you take a moment and read through [the submodules page](http://git-scm.com/book/en/Git-Tools-Submodules) in the official Git documentation. When you're ready to dive in, the following command generates a clone of WPSettings as a submodule:

```bash
git submodule add https://github.com/sergeliatko/wpsettings wpsettings
```

Do not forget the required libraries:

```bash
git submodule add https://github.com/sergeliatko/form-fields form-fields
```
```bash
git submodule add https://github.com/sergeliatko/html html
```

### Manually
Download zip files for all necessary libraries:

* [WPSettings](https://github.com/sergeliatko/wpsettings)
* [FormFields](https://github.com/sergeliatko/form-fields)
* [HTML](https://github.com/sergeliatko/html)

And extract them in your project ressources folder.

## Loading the framework classes

### Using composer
Composer will load the framework automatically.

### Manually
If loading the classes manually (after manual installation or after installation with git), make sure the autoload.php files in all 3 libraries are included in your project:

```php
<?php
//...
//Load WPSettings Framework
require_once ( dirname(__FILE__) . '/path_to/wpsettings/autoload.php' );
require_once ( dirname(__FILE__) . '/path_to/form-fields/autoload.php' );
require_once ( dirname(__FILE__) . '/path_to/html/autoload.php' );
//...
```

## Basic usage

### Adding a setting
```php
<?php
//...
//make use of the Setting class once in your file
use \SergeLiatko\WPSettings\Setting;

//...

//then create setting like this
$my_option = Setting::createInstance( array(
	'option' => 'option_name_in_db',
	'label'  => __( 'My option label', 'my-text-domain' )
) );
//...
```
Please see [src/Setting.php](https://github.com/sergeliatko/wpsettings/blob/master/src/Setting.php) for additional details and accepted parameters.

### Adding a settings section
```php
<?php
//...
//make use of the Section class once in your file
use SergeLiatko\WPSettings\Section;

//...

//then create settings section like this
$my_section = Section::createInstance( array(
	'id'          => 'custom-section-id',
	'title'       => __( 'My section title', 'my-text-domain' ),
	'description' => __( 'This is section description text that appears above setting fields.', 'my-text-domain' ),
	'settings'    => array(
		array(
			'option' => 'option_1_name_in_db',
			'label'  => __( 'My option 1 label', 'my-text-domain' ),
		),
		array(
			'option' => 'option_2_name_in_db',
			'label'  => __( 'My option 2 label', 'my-text-domain' ),
		),
	),
) );
//...
```
Please see [src/Section.php](https://github.com/sergeliatko/wpsettings/blob/master/src/Section.php) for additional details and accepted parameters.

### Adding admin page
```php
<?php
//...
//make use of the Page class once in your file
use SergeLiatko\WPSettings\Page;

//...
//then create admin page like this
$my_section = Page::createInstance( array(
	'slug'     => 'my-admin-page',
	'label'    => __( 'My Admin Page', 'my-text-domain' ),
	'sections' => array(
		array(
			'id'          => 'default',
			'title'       => __( 'My section title', 'my-text-domain' ),
			'description' => __( 'In this section my setting fields will appear', 'my-text-domain' ),
			'settings'    => array(
				array(
					'option' => 'option_1_name_in_db',
					'label'  => __( 'My option 1 label', 'my-text-domain' ),
				),
				array(
					'option' => 'option_2_name_in_db',
					'label'  => __( 'My option 2 label', 'my-text-domain' ),
				),
			),
		),
	),
) );
//...
```
Please see [src/Page.php](https://github.com/sergeliatko/wpsettings/blob/master/src/Page.php) for additional details and accepted parameters.

### Extending the core functionality
Following classes may be extended: 

* [Setting](https://github.com/sergeliatko/wpsettings/blob/master/src/Setting.php)
* [Section](https://github.com/sergeliatko/wpsettings/blob/master/src/Section.php)
* [Page](https://github.com/sergeliatko/wpsettings/blob/master/src/Page.php)

To do so, extend the class with your code and add **_class** key with your extension class fully qualified name as value to the parameters array.

```php
$my_option = Setting::createInstance( array(
	'_class' => '\\MyNameSpace\\MySettingExtension'
	'option' => 'option_name_in_db',
	'label'  => __( 'My option label', 'my-text-domain' )
) );
```

For details see src/Factory.php

### Getting option value from database
Please use [get_option()](https://developer.wordpress.org/reference/functions/get_option/) WordPress function to get option value from the database. The framework deliberately does not save all your settings inside one option to stay as close as possible to WordPress default functionality and allow you to benefit from WordPress native hooks and filters over your options, thus keeping your code even more compatible with other WordPress functionalities.

## Documentation is on its way...
While the full documentation is still not available (in process), the code source has extensive comments and parameters descriptions to help you to get the idea of possibilities. 

It would be really helpful of you to contribute to the project documentation via README.md file edits and [posting your documentation suggestions to issues](https://github.com/sergeliatko/wpsettings/issues).

## Support WPSettings Financially
Support WPSettings and help fund the project with the [Patreon Subscription](https://www.patreon.com/sergeliatko). For a price of a coffe, the INSIDER plan gives you access to early updates and tips. I you're using the framework for your commercial projects, it is advised to choose CLUB MEMBER or VIP MEMBER plan to grant you feature voting power and personal support.

Code review, product guidelines and consulting services are also available to help you out product development. Feel free to [contact me about that to get the quote](https://sergeliatko.com/contact/).

As of today, all the funds collected will be used to cover the expenses of the project documentation.

## About
### Requirements
* [PHP](https://www.php.net/) >= 5.6.0
* [WordPress](https://wordpress.org/) >= 4.7
* [sergeliatko/form-fields](https://github.com/sergeliatko/form-fields) >= 0.0.1
* [sergeliatko/html](https://github.com/sergeliatko/html) >= 0.0.1 (required by [sergeliatko/form-fields](https://github.com/sergeliatko/form-fields))

### Feature requests, Questions, Support and Bug Reports
Please submit your questions and requests in [GitHub Issues](https://github.com/sergeliatko/wpsettings/issues).

### Licence
WPSettings is licenced under GPL-3.0. See [LICENCE](https://github.com/sergeliatko/wpsettings/blob/master/LICENSE) file for details.

### Author
Serge Liatko - <contact@sergeliatko.com> - <https://sergeliatko.com>

### Build with
* [PHPStorm](https://www.jetbrains.com/phpstorm/)
* [Wampserver](http://wampserver.aviatechno.net/)
* [WordPress](https://wordpress.org)
