<?php

require_once('./weatherModel.php');

class WeatherController
{
  private $weatherModel;

  public function __construct()
  {
    $this->weatherModel = new WeatherModel();
  }

  /**
   * uses the weather model to determine if a jacket is needed in input location
   *
   * @param string $city
   * @param string $state Optonal. state code
   * @param string $country Optional. country code ISO 3166
   * @return null
   */
  public function doINeedAJacket($city, $state = '', $country = '')
  {
    //validate user inputs
    //the api is case-insensitive

    //state must be 2 letter country code
    if (!empty($state) && strlen($state) > 2) {
      $state = '';
    }

    //country must be a 2 letter country code
    if (!empty($country) && strlen($country) > 2) {
      $country = '';
    }

    //set the location data for the model
    $this->weatherModel->setLocData($city, $state, $country);

    //call the model to do the business logic
    try {
      $jacketNeeded = $this->weatherModel->isJacketNeeded();
    } catch (Exception $e) {
      echo $e->getMessage() . "\n";
      return;
    }

    //print the answer
    echo $jacketNeeded ? "Yes\n" : "No\n";
  }
}
