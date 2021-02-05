<?php

class WeatherModel
{

  /**
   * apiKey - ideally this would be set in an env variable. for ease of use its hard set here.
   * @var string
   */
  private $apiKey = '72dd58cc659e812a6ff18d1dff98531f';

  /**
   * api url
   * @var string
   */
  private $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';

  /**
   * weather data from api
   * @var array
   */
  private $weatherData = [];

  /**
   * location data used with api
   * @var array
   */
  private $locData = [];

  /**
   * set the location to retrieve weather data
   * 
   * @param string $city name of city
   * @param string $stateCode Optional. two letter state code. USA only
   * @param string $countryCode Optional. two letter country code as per ISO 3166
   */
  public function setLocData($city, $stateCode = '', $countryCode = '')
  {
    //enforce only having US states
    if (strtoupper($countryCode) != 'US') {
      $stateCode = '';
    }

    $this->locData = [
      'city' => $city,
      'state' => $stateCode,
      'country' => $countryCode
    ];

    //clear the weatherdata to force the api to fetch new data
    $this->weatherData = [];
  }

  /**
   * @return array current set location data
   */
  public function getLocData()
  {
    return $this->locData;
  }

  /**
   * determines if a jacket is needed for the weather of set city
   *
   * @return boolean if a jacket is needed or not
   */
  public function isJacketNeeded()
  {

    $data = $this->getWeatherData();

    //throw error if we cannot get the weather
    if (empty($data) || empty($data['main'])) {
      throw new \Exception("No weather data found");
    }

    //jackets could be worn due to temperature, precipitation (rain or snow), wind
    //for the purposes of knowing when to wear a jacket the feels like temperature is more important than the actual temperature
    $temperature = $data['main']['feels_like'];

    //precipitation is either present or not. the amount is not being considered here
    $precipitationPresent = isset($data['snow']) || isset($data['rain']);

    //wind will be measured with wind speed and not the direction or gust
    $windSpeed = isset($data['wind']) ? $data['wind']['speed'] : 0;

    $jacketRequired = false;
    //Temperature below 10 C requires a jacket
    //any precipitation requires a jacket
    //Wind above 10 m/s requires a jacket. classified as a strong breeze on the Beaufort scale
    if ($temperature < 10 || $precipitationPresent || $windSpeed > 10) {
      $jacketRequired = true;
    }

    return $jacketRequired;
  }

  /**
   * returns weather data if set or fetches new data
   * 
   * @param string $city
   * @return array the full weather api dataset.
   *   return data described at https://openweathermap.org/current#current_JSON
   */
  private function getWeatherData()
  {
    $weatherData = $this->weatherData;

    //weather data should only be refreshed every 10 minutes as per API docs
    //if the weather data is still current return it
    if (!empty($weatherData) && $weatherData['dt'] > (time() - 60 * 10)) {
      return $weatherData;
    }

    //throw exception when no city set
    if (empty($this->locData) || empty($this->locData['city'])) {
      throw new Exception('No location found');
    }

    //build an array for the location and remove any empty segments
    $locInfo = array_filter($this->locData);

    //split the location info into a comma separated string
    $locData = implode(',', $locInfo);

    $data = $this->callWeatherApi($locData);

    return $data;
  }

  /**
   * calls the Openweather API
   * 
   * @param string $locString the location data to query.
   *  $locString = (string) city,state code,country code. 
   * 
   * @return array associative array data returned by api
   */
  private function callWeatherApi($locString)
  {
    //load the query params in the format that the api requires
    $queryParams = [
      'q' => $locString,
      'appid' => $this->apiKey,
      'mode' => 'json',
      'units' => 'metric'
    ];

    //build the url from the base apiUrl and query params
    $url = $this->apiUrl . '?' . http_build_query($queryParams);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $res = curl_exec($ch);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($res, true);
    if ($httpcode != 200) {
      throw new Exception("Error {$httpcode}. {$data['message']}");
    }
    return $data;
  }
}
