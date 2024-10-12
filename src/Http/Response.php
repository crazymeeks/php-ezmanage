<?php

namespace Crazymeeks\PhpEzmanage\Http;

class Response
{

    public $data = null;

    public $message;

    public $status_code;

    public $success = false;

    public function __construct(array $responses)
    {
        $this->map($responses);
    }

    private function map(array $responses)
    {
        foreach($responses as $property => $value){
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }
}