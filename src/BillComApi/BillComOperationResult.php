<?php

namespace BillComApi;

class BillComOperationResult
{
    /**
     * Response status code.
     * @var int
     */
    private $status;
    /**
     * Response message.
     * @var string
     */
    private $message;
    /**
     * Response data.
     * @var mixed
     */
    private $data = null;

    /**
     * Constructor.
     *
     * @param int $status response status code
     * @param string $message response message
     * @param mixed $data response data
     */
    public function __construct($status, $message, $data = null)
    {
        $this->status = $status;
        $this->message = $message;
        if (!empty($data)) {
            $this->data = $data;
        }
    }

    /**
     * Determines whether operation was a success.
     *
     * @return bool true if success, false otherwise
     */
    public function succeeded()
    {
        return $this->status === BillCom::RESPONSE_STATUS_SUCCESS;
    }

    /**
     * Gets response message.
     *
     * @return string response message.
     */
    public function get_message()
    {
        return $this->message;
    }

    /**
     * Gets response data.
     *
     * @return mixed response data.
     */
    public function get_data()
    {
        return $this->data;
    }
}