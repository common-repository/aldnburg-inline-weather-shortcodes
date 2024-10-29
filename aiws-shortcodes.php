<?php
/**
 *
 * 
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
 
// This is, where all the shortcodes are defined 
 
class aiws_weather_shortcode {
	
	// ================ Define properties ======================
	
	// URL for Data
	
	public $weather_request_string_url_current = 'http://api.openweathermap.org/data/2.5/weather';
	public $weather_request_string_url_forecast = 'http://api.openweathermap.org/data/2.5/forecast/daily';

	
	// ================ Add shortcode hooks =====================
	
	public function __construct()
	{
		// Temperature shortcode
		
		add_shortcode( 'temp', array($this, 'temp_current') ); // Parameters: citycode
		add_shortcode( 'tempDay', array($this, 'temp_forecast') ); // Parameters: citycode, day
		
		
		// Fetch api, citycode and units to define properties
	
		$this->aiws_default_city = get_option('aiws_default_city');
		$this->api_key = get_option('aiws_api_key');
		$this->units = get_option('aiws_units');
		
	}
	
	// ================ Establish connection and API Request =================
	
	public function connect_and_request_current($data)
	{
		
		if(!isset($this->units) || $this->units == '')
		{
			$this->units = "metric";
		}
		// Connection				
					
		$this->weather_request_string = $this->weather_request_string_url . "?id=" . $this->data['citycode'] . "&units=" . $this->units . "&APPID=" . $this->api_key;
		$this->weather_request = wp_remote_get($this->weather_request_string);
		
		// Error handling

		// WP Error if no connection
		if(is_wp_error($this->weather_request))
		{
			
			// Error, which is returned to user
			$this->error = __('[error 870]','aiws-weather');
		}
		else
		{
			// Fetching data
			
			$request_data = json_decode($this->weather_request['body']);
			if(isset($request_data->cod) AND $request_data->cod == 200)
			{
				$this->weather_data = $request_data;
				$this->error = '';
			}
			else
			{
				$this->error = __('[error 872]','aiws-weather');	
			}
		}

	}
	
	
	// ================= Current Temperature Function ====================
	
	public function temp_current( $temp_data )
	{
		
		// Define URL for current data
		$this->weather_request_string_url = $this->weather_request_string_url_current;

		// Get optional data
		$this->data = shortcode_atts(array('citycode' => $this->aiws_default_city), $temp_data);
		
		//Connect (returns $this->weather_data)
		$this->connect_and_request_current($temp_data);
			
		if($this->error != '')
		{
			return $this->error;
		}
		else
		{
		
			// Check for Symbol output
			$unit_symbol = '';
					
			if(get_option('aiws_display_temperature_symbol') == 'yes')
			{
				// Add unit symbol
	
				if($this->units == 'imperial')
				{
					$unit_symbol = " °F";
				}
				else
				{
					$unit_symbol = " °C";
				}
						
			}
			
			return round($this->weather_data->main->temp).$unit_symbol;
		}
		$this->error = '';
	}

	// ================= Forecast Temperature Function ====================
	
	public function temp_forecast( $tempDay_data )
	{
		
		// Define URL for current data
		$this->weather_request_string_url = $this->weather_request_string_url_forecast;

		// Get optional data
		$this->data = shortcode_atts(array('citycode' => $this->aiws_default_city, 'day' => 0), $tempDay_data);

		//Connect (returns $this->weather_data)
		$this->connect_and_request_current($tempDay_data);
		//return $this->weather_request_string;		
					
		if($this->error != '')
		{
			return $this->error;
		}
		else
		{
		
			// Check for Symbol output
			$unit_symbol = '';
					
			if(get_option('aiws_display_temperature_symbol') == 'yes')
			{
				// Add unit symbol
	
				if($this->units == 'imperial')
				{
					$unit_symbol = " °F";
				}
				else
				{
					$unit_symbol = " °C";
				}
						
			}
			
			// Get temperature for day
			
			// Filter wrong input
			
			$i = $this->data['day'];
			if( $i == 0 || $i == 1 || $i == 2 || $i == 3 || $i == 4 || $i == 5 )
			{		
				return round($this->weather_data->list[$i]->temp->day).$unit_symbol;
			}
			else
			{
				return __('[error 877]', 'aiws-weather');	
			}
		}
		$this->error = '';
	}


	
}

new aiws_weather_shortcode();



?>