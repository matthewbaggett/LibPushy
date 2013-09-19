<?php
namespace Pushy;
use Guzzle\Http\Client as GuzzleClient;

class Connector{
  private $_session;
  private $_session_key;
  private $_access_key;

  public function __construct($access_key){
    $this->_access_key = $access_key;
  }

  private function __session_begin(){
    $client = new GuzzleClient();

    // Create a request that has a query string and an X-Foo header
    $url_session_begin = Constants::Service . "/" . Constants::Endpoint_Session_Begin;
    $url_session_begin = $this->__url_parameter_replace($url_session_begin);
    $request = $client->get($url_session_begin);

    // Send the request and get the response
    $response = $request->send();

    var_dump($response);
  }

  private function __url_parameter_replace_parameters(){
    $replacements = array();
    $replacements['%ACCESS_KEY%'] = $this->_access_key;
    $replacements['%SESSION_KEY%'] = $this->_session_key;
  }

  private function __url_parameter_replace($url){
    foreach($this->__url_parameter_replace_parameters() as $parameter_name => $parameter_value){
      $url = str_replace($parameter_name, $parameter_value, $url);
    }
    return $url;
  }

  public function send_message($channel, $message){
    $this->__session_begin();
  }
}