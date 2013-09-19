<?php
namespace Pushy;

class Constants {
  const Service = 'http://pushy.fouroneone.us';
  const Endpoint_Session_Begin = 'SessionBegin/%ACCESS_KEY%/json';
  const Endpoint_Channel_List = '%SESSION_KEY%/ChannelList/json';
  const Endpoint_Message_Create = '%SESSION_KEY%/MessageCreate/%CHANNEL%/%MESSAGE%/json';
}