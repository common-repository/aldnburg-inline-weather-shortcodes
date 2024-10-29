<?php
/**
 * Plugin Name: ALDNBURG Inline Weather Shortcodes
 * Plugin URI: aldnburg.ichbinmartin.de
 * Version: 0.1
 * Author: Martin J Krisch - Webdesign & Photographie
 * Author URI: http://ichbinmartin.de
 * Description: Display wheather data (currently: temperature) for the current date or a forecast (up to five days) from openweathermap.org. 
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: aiws-weather
 * Domain Path: /languages
 * 
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class aiws_weather
{
	
	// Property $api_helper defines the text next to the API-Input field
	
	public $api_helper;
	public $aiws_default_city_helper;
	
	// Localization
	public function aiws_weather_load_plugin_textdomain() 
	{
		load_plugin_textdomain( 'aiws-weather', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	
	
	// This is self-explaining
	
	public function __construct()
	{
		// Hook for admin menu
		
		add_action('admin_menu', array($this, 'create_plugin_settings_page'));		
		
		// Register first section of settings page
		
		add_action('admin_init', array($this, 'setup_sections'));
		
		// Register fields 
		
		add_action('admin_init', array($this, 'setup_fields'));
	
		// Load localization
		
		add_action( 'plugins_loaded', array($this, 'aiws_weather_load_plugin_textdomain'));	
		
	}
	
	// ================ Register admin menu page ================= 
	
	public function create_plugin_settings_page()
	{
		// Variables 
		$page_title = __('AIWS Weather Settings', 'aiws-weather');
		$menu_title = __('AIWS Weather','aiws-weather');
		$capability = "manage_options";
		$slug = "aiws_weather";
		$callback = array($this, 'plugin_settings_page_content');
		
		add_submenu_page('options-general.php',$page_title, $menu_title, $capability, $slug, $callback);
	}
	
	// ================ Setup Sections ================

	public function setup_sections()
	{
		add_settings_section('aiws_first_section', __('Account Settings', 'aiws-weather'), array($this, 'section_callback'), 'aiws_weather');	
	}
	
	// ================ Setup Fields ================

	public function setup_fields()
	{
		add_settings_field('aiws_api_key', __('Your API Key', 'aiws-weather'), array($this, 'api_key_callback'), 'aiws_weather', 'aiws_first_section');

		add_settings_field('aiws_units', __('Choose the unit', 'aiws-weather'), array($this, 'units_callback'), 'aiws_weather', 'aiws_first_section');

		add_settings_field('aiws_display_temperature_symbol', __('Display unit symbol?', 'aiws-weather'), array($this, 'aiws_display_temperature_symbol_callback'), 'aiws_weather', 'aiws_first_section');

		add_settings_field('aiws_default_city', __('Code for default city', 'aiws-weather'), array($this, 'aiws_default_city_callback'), 'aiws_weather', 'aiws_first_section');

		
		// Security, allow saving field options from this page
		
		register_setting('aiws_weather', 'aiws_api_key');

		register_setting('aiws_weather', 'aiws_units');

		register_setting('aiws_weather', 'aiws_display_temperature_symbol');

		register_setting('aiws_weather', 'aiws_default_city');

	}

	// ================ Callback Sections ================

	
	public function section_callback()
	{
	
		// Helping text with link to OWM sign in
		
		//echo __('Get your API-Key on', 'aiws_weather')." <a href='https://home.openweathermap.org/users/sign_in' target='_blank'>www.openweathermap.org</a>";
		
	}

	// ================ Callback Fields ================

	public function api_key_callback($arguments)
	{
	
		// This is, where the input field for the api key goes
		
		echo '<input name="aiws_api_key" id="aiws_api_key" type="text" value="'.get_option('aiws_api_key').'" />';
		printf( '<span class="api_helper"> %s</span>', $this->api_helper ); 
		$api_additional_help = 	 __('Get your API-Key on', 'aiws-weather')." <a href='https://home.openweathermap.org/users/sign_in' target='_blank'>www.openweathermap.org</a>";

		printf( '<p class="description">%s</p>', $api_additional_help ); 

		
	}

	public function units_callback($arguments)
	{
		// Fetch existing option:
		$checked = get_option('aiws_units');
		$checked_imperial = '';
		$checked_metric = '';
		if($checked == 'imperial')
		{
			$checked_imperial = "checked";
		}
		if($checked == 'metric')
		{
			$checked_metric = "checked";
		}
		
		// This is, where the input field for the unit goes
		
		echo '<input name="aiws_units" id="metric" type="radio" value="metric" '. $checked_metric .' /><label for="metric">' . __('Metric', 'aiws-weather') . ' °C</label>&nbsp;&nbsp; <input name="aiws_units" id="imperial" type="radio" value="imperial" '. $checked_imperial .'/><label for="imperial">' . __('Imperial','aiws-weather') . ' °F</label>';
		
	}

	public function aiws_display_temperature_symbol_callback($arguments)
	{
		// Fetch existing option:
		$checked = get_option('aiws_display_temperature_symbol');
		$checked_yes = '';
		$checked_no = '';
		if($checked == 'yes')
		{
			$checked_yes = "checked";
		}
		if($checked == 'no')
		{
			$checked_no = "checked";
		}
		
		// Radio buttons if you want to display symbols or not
		
		echo '<input name="aiws_display_temperature_symbol" id="yes" type="radio" value="yes" '. $checked_yes .' /><label for="yes">' . __('Yes', 'aiws-weather') . ' </label>&nbsp;&nbsp; <input name="aiws_display_temperature_symbol" id="no" type="radio" value="no" '. $checked_no .'/><label for="no">' . __('No', 'aiws-weather') . '</label>';
		
	}



	public function aiws_default_city_callback($arguments)
	{
	
		// This is, where the input field for the default city goes
		
		echo '<input name="aiws_default_city" id="aiws_default_city" type="text" value="'.get_option('aiws_default_city').'" />';
		
		$additional_help_aiws_default_city = __('This city is used, if you omit the optional parameter "citycode" in your shortcode. Useful for repeating weather data of one specific city throughout the blog.', 'aiws-weather');

		printf( '<span class="aiws_default_city_helper"> %s</span>', $this->aiws_default_city_helper ); 

		printf( '<p class="description">%s</p>', $additional_help_aiws_default_city ); 
		
	}



	// ================ Create the settings page =========
	
	// This is, where all the content is put together and the API-Key is checked

	public function plugin_settings_page_content()
	{
	?>
	<div class="wrap">
		<h1>Inline Weather Shortcodes</h1>	
		<form method="post" action="options.php">
			<?php
				
				// Test API-Key and server connection
				
				// Fetch existing API-Key
				
				$api_key = get_option('aiws_api_key');
				
				// API-Key found?
				
				if(!isset($api_key) || $api_key == '') 
				{
					$this->api_helper = '<span style="color:red;">'.__('No API-Key set.', 'aiws-weather')."</span>";
				}
				else
				{
					// Now try to fetch weather data from London (id=2643743)
					
					// First try connection
					
					$citycode = get_option('aiws_default_city');				
					
					$weather_request_string = "http://api.openweathermap.org/data/2.5/weather?id=" . $citycode . "&APPID=" . $api_key;
					$weather_request = wp_remote_get($weather_request_string);
	
					if(is_wp_error($weather_request)) // Does the request return an error-object?
					{
						$this->api_helper = '<span style="color:red;">'.__('Error trying to connect to the server.','aiws-weather')."</span>";
					}
					else
					{
						// The connection seems to be fine
						
						// Second try fetching data from json
						
						$weather_data = json_decode($weather_request['body']);
						
						// If default city wasn't found
						
						if( isset($weather_data->cod) AND $weather_data->cod == 404)
						{
							$this->api_helper = '<span style="color:green;">'.__('This API-Key is valid.', 'aiws-weather')."</span>";
							if(isset($citycode) && $citycode != '')
							{
								$this->aiws_default_city_helper = '<span style="color:red;">'.__('City not found.', 'aiws-weather').'</span>';
							}
						}
						
						// If the API-Key is wrong
						
						elseif( isset($weather_data->cod) AND $weather_data->cod == 401)
						{
							$this->api_helper = '<span style="color:red;">'.__('This API-Key is not valid.', 'aiws-weather')."</span>";
						}
						
						// If the status code is allright (cod == 200)
						
						elseif( isset($weather_data->cod) AND $weather_data->cod == 200)
						{
							$this->api_helper = '<span style="color:green;">'.__('This API-Key is valid.', 'aiws-weather')."</span>";
							$this->aiws_default_city_helper = '<span style="color:green;">'. $weather_data->name .", ". $weather_data->sys->country .'</span>';
						
						}
						
						// If there is another error
						
						else
						{
							$this->api_helper = __('Unknown error.', 'aiws-weather');
						}	
					}			
				}
				
				// After validating API-Key print the fields and submit button
				
				settings_fields('aiws_weather');
				do_settings_sections('aiws_weather');
				submit_button();
			
			?>
		</form>	
		<h1><?php _e('Quick help', 'aiws-weather'); ?></h1>
		<h2><?php _e('Getting started', 'aiws-weather'); ?></h2>
		<h4><?php _e('API-Key', 'aiws-weather'); ?></h4>
		<p><?php _e('The OpenWeatherMap API requires an API-Key. They offer different plans (and even a free one) for which you can sign up. Simply go to ', 'aiws-weather') ?><a href="https://home.openweathermap.org/users/sign_in" target="_blank">www.openweathermap.org</a></p>
		<h4><?php _e('Citycode', 'aiws-weather'); ?></h4>
		<p><?php _e('The OpenWeatherMap API requires a citycode to find the exact city you want to get the data from. To find the citycode, simply go to <a href="http://www.openweathermap.org" target="_blank">www.openweathermap.org</a> and search for your city. Open the site and have a look at the URL, for example:<br><code>http://openweathermap.org/city/2857458</code>. The number at the end of the URL is the citycode.', 'aiws-weather'); ?></p>
		<p><?php _e('There will be an easier way to access the citycode via this plugin in future, stay tuned!','aiws-weather'); ?></p>
		<h4><?php _e('Licensing','aiws-weather'); ?></h4>
		<p><?php _e('Read the licensing information carefully on ','aiws-weather'); ?><a href='http://openweathermap.org/price' target='_blank'>www.openweathermap.org</a>. <?php _e('Some plans might require to mention the service on your blog.', 'aiws-weather'); ?></p>
		<h2><?php _e('Shortcodes','aiws-weather'); ?></h2>
		<h4><?php _e('Current Temperature','aiws-weather'); ?></h4>
		<p><?php _e('Simply place the shortcode <code>[temp]</code> wherever you want to display the current temperature of your default city. Thats all!','aiws-weather'); ?></p>
		<p><?php _e('However, the most common use is to display the current temperature of a certain city by using an optional parameter. Simply put <code>[temp citycode="xxxxxxx"]</code> wherever you want, to get the temperature from the city you want.','aiws-weather'); ?></p>
		<h4><?php _e('Temperature forecast','aiws-weather'); ?></h4>
		<p><?php _e('To display a temperature forecast for up to five days use <code>[tempDay day="x" citycode="xxxxxxx"]</code> shortcode. Both parameters are optional, if you omit <code>day="x"</code>, the shortcode will return temperature of the current day. Possible values are <code>0 - 5</code>.','aiws-weather'); ?></p>
		<p><?php _e('If you omit <code>citycode="xxxxxxx"</code> the default citycode is used.', 'aiws-weather'); ?></p>
		
		<h4><?php _e('Other Shortcodes','aiws-weather'); ?></h4>
		<p><?php _e('More options and other shortcodes will be added permanently!','aiws-weather'); ?></p>
		
	</div>
	<?php
	}


}

new aiws_weather();

// Require the shortcode class

require_once( dirname(__FILE__) . "/aiws-shortcodes.php"); 

?>