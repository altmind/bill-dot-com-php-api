<?php
/**
 * Bill.com JSON API client.
 *
 * @author Eric Chiang <eric.chiang@spreemo.com>
 * @author John Peloquin <john.peloquin@spreemo.com>
 * @copyright Copyright (c) 2013 Spreemo LLC <http://www.spreemo.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
/**
 * Bill.com JSON API client.
 */
namespace BillComApi;

class BillCom
{
    /**
     * Response status codes.
     */
    const RESPONSE_STATUS_SUCCESS = 0;
    const RESPONSE_STATUS_ERROR = 1;
    /**
     * Object types.
     */
    const BILL = "Bill";
    const BILL_PAYMENT = "SentPay";
    const VENDOR = "Vendor";
    /**
     * Payment status codes.
     */
    const BILL_PAID = 0;
    const BILL_UNPAID = 1;
    const BILL_PARTIALLY_PAID = 2;
    const BILL_SCHEDULED = 4;

    const BILL_ENDPOINT_PRODUCTION = "https://api.bill.com/api/v2/";
    const BILL_ENDPOINT_STAGING = "https://app-stage.bill.com/api/v2/";

    /**
     * Debug mode.
     * @var bool
     */
    private $debug = false;
    /**
     * Host.
     * @var string
     */
    private $host = "https://api.bill.com/api/v2/";
    /**
     * Developer key.
     * @var string
     */
    private $dev_key = null;
    /**
     * Password.
     * @var string
     */
    private $password = null;
    /**
     * Username.
     * @var string
     */
    private $user_name = null;
    /**
     * Organization ID.
     * @var string
     */
    private $org_id = null;
    /**
     * Session ID.
     * @var string
     */
    private $session_id = null;

    /**
     * Constructor.
     *
     * @param string $dev_key developer key
     * @param string $password password
     * @param string $user_name username
     * @param string|null $host host
     * @param string|null $org_id organization ID
     */
    public function __construct($dev_key, $password, $user_name, $host = null, $org_id = null)
    {
        $this->dev_key = $dev_key;
        $this->password = $password;
        $this->user_name = $user_name;
        if (!empty($host)) {
            $this->host = $host;
        }
        if (!empty($org_id)) {
            $this->org_id = $org_id;
        }
    }

    /**
     * Logs in.
     *
     * Logs into first organization returned from {@link list_orgs()} by default.
     * Establishes session for subsequent operations until {@link logout()}.
     *
     * @param string|null $org_id organization ID
     * @return BillComOperationResult result
     * @throws BillComException
     */
    public function login($org_id = null)
    {
        if (!empty($org_id)) {
            $this->org_id = $org_id;
        } elseif (empty($this->org_id)) {
            // by default, pick the first org_id from the list of organizations
            $org_list = $this->list_orgs();
            $this->org_id = $org_list[0]['orgId'];
        } else {
            // use pre-existing $this->org_id
        }
        $result = $this->do_request(
            $this->host . "Login.json",
            array(
                'userName' => $this->user_name,
                'password' => $this->password,
                'orgId' => $this->org_id,
                'devKey' => $this->dev_key,
            )
        );
        if ($result->succeeded()) {
            $response_data = $result->get_data();
            $this->session_id = $response_data['sessionId'];
            return $result;
        } else {
            throw new BillComException(sprintf(
                "Error when logging in: userName='%s', ordId='%s', double check password and devKey. response details:\n%s",
                $this->user_name,
                $this->org_id,
                var_export($result, true)
            ));
        }
    }

    /**
     * Logs out.
     *
     * Terminates session.
     *
     * @return BillComOperationResult result
     * @throws BillComException
     */
    public function logout()
    {
        $result = $this->do_request(
            $this->host . "Logout.json",
            array(
                'sessionId' => $this->session_id,
                'devKey' => $this->dev_key,
            )
        );
        if ($result->succeeded()) {
            $this->session_id = null;
            return $result;
        } else {
            throw new BillComException(sprintf(
                "Error when logging out: response details:\n%s",
                var_export($result, true)
            ));
        }
    }

    /**
     * Lists organizations.
     *
     * @return array organizations
     * @throws BillComException
     */
    public function list_orgs()
    {
        $result = $this->do_request(
            $this->host . "ListOrgs.json",
            array(
                'userName' => $this->user_name,
                'password' => $this->password,
                'devKey' => $this->dev_key,
            )
        );
        if ($result->succeeded()) {
            return $result->get_data();
        } else {
            throw new BillComException(sprintf(
                "Error when listing organizations: userName='%s', double check password and devKey. response details:\n%s",
                $this->user_name,
                var_export($result, true)
            ));
        }
    }

    /**
     * Lists objects.
     *
     * @param string $obj_url object name
     * @param array|null $options options
     * @return BillComOperationResult result
     * @throws BillComException
     */
    public function get_list($obj_url, $options = null)
    {
        if (empty($this->session_id)) {
            $this->login();
        }
        if (empty($options)) {
            $options = array('start' => 0, 'max' => 999,);
        }
        $result = $this->do_request(
            $this->host . "List/$obj_url.json",
            array(
                'devKey' => $this->dev_key,
                'sessionId' => $this->session_id,
                'data' => json_encode($options),
            )
        );
        if ($result->succeeded()) {
            return $result->get_data();
        } else {
            throw new BillComException(sprintf(
                "Error during crud operation: '%s', on obj type: '%s', opts:\n%s\nresponse details:\n%s",
                "List",
                $obj_url,
                var_export($options, true),
                var_export($result, true)
            ));
        }
    }

    /**
     * Creates an object.
     *
     * @param string $obj_url object name
     * @param array|null $obj_facts object data
     * @return object result data
     * @throws BillComException
     */
    public function create($obj_url, $obj_facts)
    {
        return $this->crud('Create', $obj_url, array('obj' => $obj_facts));
    }

    /**
     * Reads an object.
     * @param string $obj_url object name
     * @param string $obj_id object ID
     * @return object result data
     * @throws BillComException
     */
    public function read($obj_url, $obj_id)
    {
        return $this->crud('Read', $obj_url, array('id' => $obj_id));
    }

    /**
     * Updates an object.
     * @param string $obj_url object name
     * @param string $obj_facts object data
     * @return object result data
     * @throws BillComException
     */
    public function update($obj_url, $obj_facts)
    {
        return $this->crud('Update', $obj_url, array('obj' => $obj_facts));
    }

    /**
     * Pays a bill.
     * @param array data payment data
     * @return object result data
     * @throws BillComException
     */
    public function pay_bill($data)
    {
        if (empty($this->session_id)) {
            $this->login();
        }
        $result = $this->do_request(
            $this->host . "PayBill.json",
            array(
                'devKey' => $this->dev_key,
                'sessionId' => $this->session_id,
                'data' => json_encode($data),
            )
        );
        if ($result->succeeded()) {
            return $result->get_data();
        } else {
            throw new BillComException(sprintf(
                "Error during PayBill. data:\n%s\nresponse details:\n%s",
                var_export($data, true),
                var_export($result, true)
            ));
        }
    }

    /**
     * Records bill external pay.
     * @param array data payment data
     * @return object result data
     * @throws BillComException
     */
    public function record_bill_payed($data)
    {
        if (empty($this->session_id)) {
            $this->login();
        }
        $result = $this->do_request(
            $this->host . "RecordAPPayment.json",
            array(
                'devKey' => $this->dev_key,
                'sessionId' => $this->session_id,
                'data' => json_encode($data),
            )
        );
        if ($result->succeeded()) {
            return $result->get_data();
        } else {
            throw new BillComException(sprintf(
                "Error during RecordAPPayment. data:\n%s\nresponse details:\n%s",
                var_export($data, true),
                var_export($result, true)
            ));
        }
    }
    /**
     * Uploads an attachment to an object.
     *
     * @param string $associated_obj_id object ID
     * @param string $file_name filename
     * @param string $content content
     * @return object result data
     * @throws BillComException
     */
    public function upload_attachment($associated_obj_id, $file_name, $content)
    {
        if (empty($this->session_id)) {
            $this->login();
        }
        $result = $this->do_request(
            $this->host . "UploadAttachment.json",
            array(
                'devKey' => $this->dev_key,
                'sessionId' => $this->session_id,
                'data' => json_encode(
                    array(
                        'id' => $associated_obj_id,
                        'fileName' => $file_name,
                        'document' => utf8_encode(base64_encode($content)),
                    )
                ),
            )
        );
        if ($result->succeeded()) {
            return $result->get_data();
        } else {
            throw new BillComException(sprintf(
                "Error during upload attachment. associated obj id: '%s', filename: '%s'\nresponse details:\n%s",
                $associated_obj_id,
                $file_name,
                var_export($result, true)
            ));
        }
    }

    /**
     * Performs create, read, update, or delete operation.
     *
     * @param string $action_name action name
     * @param string $obj_url object name
     * @param array $data object data
     * @return object result data
     * @throws BillComException
     */
    private function crud($action_name, $obj_url, $data)
    {
        if (empty($this->session_id)) {
            $this->login();
        }
        $result = $this->do_request(
            $this->host . "Crud/" . $action_name . "/" . $obj_url . ".json",
            array(
                'devKey' => $this->dev_key,
                'sessionId' => $this->session_id,
                'data' => json_encode($data),
            )
        );
        if ($result->succeeded()) {
            return $result->get_data();
        } else {
            throw new BillComException(sprintf(
                "Error during crud operation: '%s', on obj type: '%s', data:\n%s\nresponse details:\n%s",
                $action_name,
                $obj_url,
                var_export($data, true),
                var_export($result, true)
            ));
        }
    }

    /**
     * Performs API request.
     *
     * @param string $address address
     * @param array $params parameters
     * @return BillComOperationResult result
     * @throws BillComException
     */
    private function do_request($address, $params)
    {
        $ch = curl_init($address);
        if ($this->debug) {
            echo "Request address: $address?" . http_build_query($params) . "\n";
        }
        $result = $this->curlPost($address, $params);
        if ($this->debug) {
            echo "Response: \n";
            var_dump($result);
            echo "\n";
        }
        if ($result = json_decode($result, true)) {
            return new BillComOperationResult($result['response_status'], $result['response_message'],
                $result['response_data']);
        } else {
            return new BillComOperationResult(self::RESPONSE_STATUS_ERROR, "No data received from Bill.com service.");
        }
    }

    /**
     * Performs HTTP POST request with cURL.
     *
     * @param string $host host
     * @param array $params parameters
     * @return mixed result on success, false on error
     * @throws BillComException
     */
    private function curlPost($host, $params = array())
    {
        $handle = curl_init($host);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        // NOTE: On Windows systems, the line below may cause problems even when
        //       cURL is configured with a CA file.

        $is_windows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, !$is_windows);

        curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($handle);
        if ($result === false) {
            throw new BillComException('Curl error: ' . curl_error($handle));
        }
        return $result;
    }

}
