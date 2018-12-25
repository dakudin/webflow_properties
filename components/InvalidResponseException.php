<?php

namespace app\components;

use yii\base\Exception;

/**
 * InvalidResponseException represents an exception caused by invalid remote server response.
 *
 */
class InvalidResponseException extends Exception
{
    /**
     * @var \yii\httpclient\Response HTTP response instance.
     */
    public $response;


    /**
     * Constructor.
     * @param \yii\httpclient\Response $response response body
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($response, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }
}