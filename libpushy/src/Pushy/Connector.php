<?php
namespace Pushy;
use Guzzle\Http\Client as GuzzleClient;

class Connector{
  private function __session_begin(){
    $client = new GuzzleClient();

    // Create a request that has a query string and an X-Foo header
    $url_session_begin = Constants::Service . "/" . Constants::Endpoint_Session_Begin;
    $request = $client->get($url_session_begin);

    // Send the request and get the response
    $response = $request->send();

    var_dump($response);
  }
}