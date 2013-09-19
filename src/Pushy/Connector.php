<?php
namespace Pushy;

use Guzzle\Http\Client as GuzzleClient;

class Connector {
  private $_session;
  private $_session_key = "NO SESSION KEY";
  private $_session_key_expirey;
  private $_access_key = "NO ACCESS KEY";
  private $_guzzle;
  private $_guzzle_response;

  public function __construct($access_key) {
    $this->_access_key = $access_key;
    $this->_guzzle = new GuzzleClient();
  }

  private function __make_request($url) {
    $request = $this->_guzzle->get($url);

    // Send the request and get the response
    $this->_guzzle_response = $request->send();
    if (!$this->_guzzle_response->getStatusCode() == 200) {
      throw new \Pushy\AccessException("Accessing {$url} responded {$this->_guzzle_response->getStatusCode()}");
    }

    return $this->_guzzle_response->getBody(true);
  }

  private function __session_key_expirey_limit() {
    // Hopefully, this will take account of some local-time drift...
    return time() - 60;
  }

  private function __url_parameter_replace_parameters($options = null) {
    $replacements = array();
    $replacements['%ACCESS_KEY%'] = $this->_access_key;
    $replacements['%SESSION_KEY%'] = $this->_session_key;
    if(count($options) > 0){
      foreach($options as $k => $v){
        $replacements[$k] = $v;
      }
    }
    return $replacements;
  }

  private function __url_parameter_replace($url, $options = null) {
    foreach ($this->__url_parameter_replace_parameters($options) as $parameter_name => $parameter_value) {
      $url = str_replace($parameter_name, $parameter_value, $url);
    }
    return $url;
  }

  private function __session_begin() {
    if ($this->_session_key_expirey <= $this->__session_key_expirey_limit()) {

      // Create a request that has a query string and an X-Foo header
      $url_session_begin = Constants::Service . "/" . Constants::Endpoint_Session_Begin;
      $url_session_begin = $this->__url_parameter_replace($url_session_begin);

      $response_object = json_decode($this->__make_request($url_session_begin));

      $this->_session_key = $response_object->session_key;
      $this->_session_key_expirey = strtotime($response_object->expires_at);
    }
  }

  public function create_channel($channel){
    $this->__session_begin();
    $url = $this->__url_parameter_replace(
      Constants::Service . "/" . Constants::Endpoint_Channel_Create,
      array(
           '%CHANNEL%' => $channel
      )
    );

    $response_object = json_decode($this->__make_request($url));

    if(isset($response_object->state)){
      if($response_object->state !== "OKAY"){
        throw new AccessException("Could not create channel. {$response_object->message}");
      }
    }

    return $response_object;
  }

  public function send_message($channel, $message) {
    $this->__session_begin();

    try{
      $this->create_channel($channel);
    }catch(AccessException $e){
      // Do nothing.
    }

    $url = $this->__url_parameter_replace(
      Constants::Service . "/" . Constants::Endpoint_Message_Create,
      array(
           '%MESSAGE%' => $message,
           '%CHANNEL%' => $channel
      )
    );

    $response_object = json_decode($this->__make_request($url));

    if($response_object->message !== $message){
      throw new AccessException("Could not send message: {$response_object->message}.");
    }

    return $response_object;

  }
}