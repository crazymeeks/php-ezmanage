<?php

namespace Crazymeeks\PhpEzmanage\Contract;

interface QueryInterface
{

    /**
     * Get graphql query
     * 
     * @return string
     */
    public function get();

    /**
     * Data setter
     * 
     * @param mixed $args
     * 
     * @return $this
     */
    public function setData($args);

    /**
     * Getter
     * 
     * @return mixed
     */
    public function getData();
}