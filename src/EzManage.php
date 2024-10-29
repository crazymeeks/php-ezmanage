<?php

namespace Crazymeeks\PhpEzmanage;

use Crazymeeks\PhpEzmanage\Http\Curl;
use Crazymeeks\PhpEzmanage\Contract\QueryInterface;

class EzManage
{

    /**
     * ezcater base api url.
     * 
     * Can be override via $config
     * 
     * @var string
     */
    protected $base_url = 'https://api.ezcater.com';

    /**
     * Prefix that will be appended to $base_url
     * 
     * @var string
     */
    protected $base_url_prefix = 'graphql';

    /**
     * The API Token
     * 
     * @var string
     */
    protected $authorization = null;

    protected $client_name = 'Crazymeeks EzManage';

    protected $client_version = '1.0';

    protected Curl $curl;

    protected $response_object = false;

    protected $is_json = false;

    protected $is_debug = false;

    protected $as_array = false;

    /**
     * Constructor
     * 
     * @param \Crazymeeks\PhpEzmanage\Http\Curl $curl
     */
    public function __construct(Curl $curl = null, array $configs = [])
    {
        $this->map($configs);
        $this->curl = !$curl ? new Curl() : $curl;
    }

    /**
     * Map configs
     * 
     * @return void
     */
    private function map(array $configs = [])
    {
        foreach($configs as $property => $value){
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Set base url
     * 
     * @param string $base_url
     * 
     * @return $this
     */
    public function set_base_url(string $base_url)
    {
        $this->base_url = $base_url;
        return $this;
    }

    /**
     * Set base url prefix
     * 
     * @param string $base_url_prefix
     * 
     * @return $this
     */
    public function set_base_url_prefix(string $base_url_prefix)
    {
        $this->base_url_prefix = $base_url_prefix;
        return $this;
    }

    /**
     * Set authorization header
     * 
     * @param string $authorization The api token from ezManage
     * 
     * @return $this
     */
    public function set_authorization(string $authorization)
    {
        $this->authorization = $authorization;
        return $this;
    }

    /**
     * Set client name
     * 
     * @param string $client_name
     * 
     * @return $this
     */
    public function set_client_name(string $client_name)
    {
        $this->client_name = $client_name;
        return $this;
    }

    /**
     * Set client version
     * 
     * @param string $client_version
     * 
     * @return $this
     */
    public function set_client_version(string $client_version)
    {
        $this->client_version = $client_version;
        return $this;
    }

    /**
     * Get authorization header
     * 
     * @return string
     */
    public function get_authorization()
    {
        return $this->authorization;
    }

    /**
     * Get client name
     * 
     * @return string
     */
    public function get_client_name()
    {
        return $this->client_name;
    }

    /**
     * Get client version
     * 
     * @return string
     */
    public function get_client_version()
    {
        return $this->client_version;
    }

    // end getter


    /**
     * Get ezcater base url
     * 
     * @return string
     */
    public function get_base_url()
    {
        return $this->base_url;
    }

    /**
     * Get base url prefix
     * 
     * @return string
     */
    public function get_base_url_prefix()
    {
        return $this->base_url_prefix;
    }

    /**
     * Get ezcater final endpoint
     * 
     * @return string
     */
    public function final_endpoint()
    {
        $base_url = $this->get_base_url();
        $base_url_prefix = $this->get_base_url_prefix();

        if (empty($base_url)) {
            throw new \LogicException("base_url is not set.");
        }

        $api_url = sprintf("%s", rtrim($this->get_base_url()));
        if (!empty($base_url) && !empty($base_url_prefix)) {
            $api_url = sprintf("%s/%s", rtrim($this->get_base_url()), ltrim($this->get_base_url_prefix()));

        }
        return $api_url;
    }

    /**
     * Set the request as json
     * 
     * @return $this
     */
    public function as_json()
    {
        $this->is_json = true;
        return $this;
    }

    /**
     * Set response as object
     * 
     * @return $this
     */
    public function return_as_response_object()
    {
        $this->response_object = true;
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
     * Enable cURL debug
     * 
     * @return $this
     */
    public function debug()
    {
        $this->is_debug = true;

        return $this;
    }

    /**
     * Send graphql query
     * 
     * @param \Crazymeeks\PhpEzmanage\Contract\QueryInterface $query
     * 
     * @return \Crazymeeks\PhpEzmanage\Http\Response
     */
    public function send_query(QueryInterface $query)
    {
        
        $query = $query->get();
        
        $this->curl->to($this->final_endpoint())
                         ->with_headers($this->get_required_headers())
                         ->with_data([
                            'query' => $query
                         ]);
        if ($this->is_json) {
            $this->curl->as_json();
        }
        if ($this->is_debug) {
            $this->curl->debug();
        }
        
        if ($this->as_array) {
            $this->curl->return_as_array();
        }

        if ($this->response_object) {
            $this->curl->return_as_response_object();
        }
        
        $response = $this->curl->post();

        return $response;
    }

    /**
     * Get ezcater's required headers
     * 
     * @return array
     */
    public function get_required_headers()
    {
        if (!$authorization = $this->get_authorization()) {
            throw new \LogicException("Authorization is required.");
        }
        return [
            'Authorization' => $authorization,
            'Apollographql-client-name' => $this->get_client_name(),
            'Apollographql-client-version' => $this->get_client_version()
        ];
    }

}