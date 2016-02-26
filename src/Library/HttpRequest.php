<?php

namespace Library;

class HttpRequest
{
    private $_url;
    private $_options;
    private $_data;
    private $_info;

    public function __construct($url, $method="")
    {
      $this->_url = $url;
    }

    public function setUrl($url)
    {
      $this->_url = $url;
    }

    public function setOptions($options)
    {
      $this->_options=$options;
    }

    public function addPostFields($data)
    {
      $this->_data=$data;
    }

    public function getResponseCode()
    {
      return $_data;
    }


    public function send()
    {
      $ch = curl_init();
      if (!empty($this->_options))
      {
        $httpAuth=$this->_options['httpauth'];
        curl_setopt($ch, CURLOPT_USERPWD, $httpauth);
      }
      if (!empty($this->_data))
      {
        foreach($this->_data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
      }
      curl_setopt($ch, CURLOPT_URL,  $this->_url );
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      $res = curl_exec($ch);
      curl_close($ch);

      $this->info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      return $res;
    }


    public function _send(array $data = array())
    {
        $this->addQueryData($data);
        try {
            parent::send();
            $status = $this->getResponseCode();
            switch ($status) {
                case 200:
                case 302:
                    return array(
                        'status' => $this->getResponseStatus(),
                        'body' => $this->getResponseBody()
                    );
                    break;
                case 401:
                case 403:
                    throw new \Exception("Response xxx 403");
                    break;
            }
        } catch (\HttpException $exception) {
            return false;
        }
    }
}
