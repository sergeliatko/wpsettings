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

## Documentation is coming...
The code source has extensive comments and parameters descriptions, but it would be really helpful of you to contribute to the project documentation via README.md file edits and [posting to issues](https://github.com/sergeliatko/wpsettings/issues).

## Support WPSettings Financially
Get WPSettings supported and help fund the project with the [Patreon Subscription](https://www.patreon.com/sergeliatko). As of today, all the funds collected will be used to cover the expenses of project documentation.

## About
### Requirements
* [PHP](https://www.php.net/) >= 5.6.0
* [WordPress](https://wordpress.org/) >= 4.7
* [sergeliatko/form-fields](https://github.com/sergeliatko/form-fields) >= 0.0.1

### Feature requests, Questions, Support and Bug Reports
Please submit your questions and requests in [GitHub Issues](https://github.com/sergeliatko/wpsettings/issues).

### Licence
WPSettings is licenced under GPL-3.0 licence. See [LICENCE](https://github.com/sergeliatko/wpsettings/blob/master/LICENSE) file for details.

### Author
Serge Liatko - <contact@sergeliatko.com> - <https://sergeliatko.com>

### Build with
* [PHPStorm](https://www.jetbrains.com/phpstorm/)
* [Wampserver](http://wampserver.aviatechno.net/)
* [WordPress](https://wordpress.org)
