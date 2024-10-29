<?php

namespace Crazymeeks\PhpEzmanage\Query;

use Crazymeeks\PhpEzmanage\Contract\QueryInterface;
use Crazymeeks\PhpEzmanage\Query\BaseQuery;

class AllCatererQuery extends BaseQuery implements QueryInterface
{

    /** @inheritDoc */
    public function setData($args)
    {
        $this->data = $args;
        return $this;
    }

    /** @inheritDoc */
    public function getData()
    {
        return $this->data;
    }

    /** @inheritDoc */
    public function get()
    {

        $queryString = '
        query allCaterers {
            caterers {
                uuid
                name
                storeNumber
                live
                address {
                    street
                    street2
                    state
                    stateName
                    city
                    state
                    zip
                }
            }
        }';

        return $queryString;
    }
}