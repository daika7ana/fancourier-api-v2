<?php

namespace Fancourier\Response;

class Generic implements ResponseInterface
{
    protected $errorCode;
    protected $errorMessage;
    protected $data;

    /**
     * @return mixed
     */
    #[\Override]
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     * @return Generic
     */
    #[\Override]
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $errorMessage
     * @return Generic
     */
    #[\Override]
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Generic
     */
    #[\Override]
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Record a failure from an API body.
     *
     * Accepts either the decoded body (array) or the raw response string and
     * always leaves a non-empty message and an error code behind, so isOk()
     * cannot report success on a non-success body.
     *
     * @param mixed  $body     Decoded body (array) or raw response string.
     * @param mixed  $code     Error code to store.
     * @param string $fallback Message used when the body carries none.
     * @return $this
     */
    protected function setErrorFromBody($body = null, $code = -1, string $fallback = 'Unknown error')
    {
        $message = null;

        if (is_array($body))
            {
            $message = $body['message'] ?? $body['error'] ?? $body['errors'] ?? null;
            }
        elseif (is_string($body))
            {
            $message = $body;
            }

        if (is_array($message))
            {
            $message = json_encode($message);
            }

        if (!is_string($message) || $message === '')
            {
            $message = $fallback;
            }

        return $this->setErrorMessage($message)->setErrorCode($code);
    }

    public function isOk()
    {
        return empty($this->getErrorCode()) && empty($this->getErrorMessage());
    }
}
