<?php

namespace Library;

class HttpRequest extends \HttpRequest
{
    public function send(array $data = array())
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
                    throw new Exception();
                    break;
            }
        } catch (\HttpException $exception) {
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}