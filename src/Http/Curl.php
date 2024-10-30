<?php

namespace Crazymeeks\PhpEzmanage\Http;

use Crazymeeks\PhpEzmanage\Http\Response;

class Curl
{

    protected $headers = [];

    /** @var string|null */
    protected $to_url = null;

    /** @var array */
    protected $curlData = [];

    /** @var bool */
    protected $as_json = false;

    /**
     * Determine if the response should be an object
     * 
     * @var bool
     */
    protected $response_as_object = false;

    /** @var bool */
    private $is_debug = false;

    /** @var bool */
    protected $as_array = false;

    protected int $http_response_code;


    /**
     * The address that will be sending request
     * 
     * @param string $url
     * 
     * @return $this
     */
    public function to(string $url)
    {
        $this->to_url = $url;
        return $this;
    }

    /**
     * Get url that will be receiving the request
     * 
     * @return string|null
     */
    public function get_to()
    {
        return $this->to_url;
    }

    /**
     * Add headers to the request
     * 
     * @param array $headers
     * 
     * @return $this
     */
    public function with_headers(array $headers)
    {

        foreach($headers as $name => $value){
            $this->headers[] = sprintf("%s: %s", $name, $value);
        }
        
        return $this;
    }

    /**
     * Get the headers
     * 
     * @return array
     */
    public function get_headers()
    {
        return $this->headers;
    }
    
    /**
     * cURL data
     * 
     * @param array $data
     * 
     * @return $this
     */
    public function with_data(array $data)
    {
        $this->curlData = $data;
        return $this;
    }

    /**
     * cURL post data
     * 
     * @param \CurlHandle $ch
     * 
     * @return void
     */
    protected function add_post_data(\CurlHandle $ch)
    {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->as_json ? json_encode($this->curlData) : $this->curlData);
    }

    /**
     * Send post data
     * 
     * @return \Crazymeeks\PhpEzmanage\Http\Response
     */
    public function post()
    {
        
        $to = $this->must_have_to_url();
        
        $ch = curl_init($to);
        
        // Set cURL options
        return $this->set_options($ch, __FUNCTION__)
             ->send($ch);
    }

    /**
     * Set http response code
     * 
     * @param int $response_code
     * 
     * @return void
     */
    private function set_response_http_code(int $response_code)
    {
        $this->http_response_code = $response_code;
        
    }

    /**
     * Get http response code
     * 
     * @return int
     */
    private function get_http_response_code()
    {
        return $this->http_response_code;
    }

    /**
     * Send the request
     * 
     * @param \CurlHandle $ch
     * 
     * @return \Crazymeeks\PhpEzmanage\Http\Response
     */
    public function send(\CurlHandle $ch)
    {
        $response = curl_exec($ch);

        $this->set_response_http_code(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if ($response === false) {
            return new Response([
                'message' => sprintf("cuRL Error: %s", curl_error($ch)),
                'status_code' => $this->get_http_response_code(),
            ]);
        }
        curl_close($ch);

        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        return $this->send_response($body);
    }

    private function send_response(string $body)
    {
        if ($this->is_json_decodable($body)) {
            if (strpos($body, 'errors')) {
                return new Response([
                    'message' => 'Oopps! Error. Please see response body',
                    'data' => $body,
                    'status_code' => $this->get_http_response_code(),
                    'success' => false,
                ]);
            }

            $response = $this->response_as_object ? json_decode($body) : ($this->as_array ? json_decode($body, true): $body);

            return new Response([
                'message' => 'Success',
                'status_code' => $this->get_http_response_code(),
                'success' => true,
                'data' => $response,
            ]);
        }

        return new Response([
            'message' => $body,
            'success' => !in_array($this->get_http_response_code(), [200, 201]),
            'status_code' => $this->get_http_response_code(),
            'data' => null,
        ]);
    }


    /**
     * Check if the response if a valid json
     * 
     * @param string $data The response data
     * 
     * @return bool
     */
    private function is_json_decodable(string $data)
    {
        json_decode($data);

        return (json_last_error() === JSON_ERROR_NONE);
    }


    /**
     * Set the request as json
     * 
     * @return $this
     */
    public function as_json()
    {
        $this->as_json = true;

        return $this;
    }

    /**
     * Set response as array
     * 
     * @return $this
     */
    public function return_as_array()
    {
        $this->as_array = true;

        return $this;
    }

    
    /**
     * Set response as object
     * 
     * @return $this
     */
    public function return_as_response_object()
    {
        $this->response_as_object = true;
        return $this;
    }

    /**
     * For displaying cURL response
     * 
     * @return $this
     */
    public function debug()
    {
        $this->is_debug = true;

        return $this;
    }

    /**
     * Set cURL options
     * 
     * @param \CurlHandle $ch
     * 
     * @return $this
     */
    private function set_options(\CurlHandle $ch, string $method)
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if ($method !== 'get') {
            $func = sprintf("add_%s_data", $method);
            if (!method_exists($this, $func)) {
                throw new \LogicException(sprintf("The method %s is not yet supported.", $method));
            }
            $this->{$func}($ch);
        }

        if ($this->is_debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        if (!empty($headers = $this->get_headers())) {
            if ($this->as_json) {
                $headers[] = sprintf("Content-Type: application/json");
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        return $this;
    }

    /**
     * Make sure we have the api url
     * 
     * @return string
     * 
     * @throws \LogicException
     */
    protected function must_have_to_url()
    {
        if (!$to = $this->get_to()) {
            throw new \LogicException("No url defined.");
        }

        return $to;

    }
}