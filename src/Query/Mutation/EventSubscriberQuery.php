<?php

namespace Crazymeeks\PhpEzmanage\Query\Mutation;

use Crazymeeks\PhpEzmanage\Contract\QueryInterface;
use Crazymeeks\PhpEzmanage\Query\BaseQuery;

class EventSubscriberQuery extends BaseQuery implements QueryInterface
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
        $flex_webhook_url = $this->getData();

        $queryString = sprintf('
        mutation createSubscriber {
            createSubscriber(subscriberParams: { name:
            "Flex Api Integration",
            webhookUrl: "%s"
            }) {
                subscriber {
                    id
                    name
                    webhookUrl
                    webhookSecret
                }
            }
        }
        ', $flex_webhook_url);

        return $queryString;
    }
}