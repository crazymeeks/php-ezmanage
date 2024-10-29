<?php
/**
 * Setup subscription. Currently EzCater only supports
 * subscriptions for:
 * 
 * Order Accepted: triggers once an order has been accepted,
 *                 not when the order is placed. Also
 *                 triggers when an order has been
 *                 updated & that order update
 *                 is accepted
 *                 
 * Order Cancelled:
 * Menu Updated:
 */


namespace Crazymeeks\PhpEzmanage\Query\Mutation;

use Crazymeeks\PhpEzmanage\Contract\QueryInterface;
use Crazymeeks\PhpEzmanage\Query\BaseQuery;

class SetupSubscription extends BaseQuery implements QueryInterface
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
        $data = $this->validate_create_subscription_query();
        [
            'subscriber_id' => $subscriber_id,
            'event_key' => $event_key,
            'caterer_id' => $caterer_id
        ] = $data;
        $queryString = sprintf('
        mutation createSubscription {
            createSubscription(subscriptionParams: { 
                subscriberId: "%s",
                eventKey: %s,
                eventEntity: Order,
                parentEntity: Caterer,
                parentId: "%s"
            }) {
                subscription {
                    parentEntity
                    parentId
                    eventKey
                    eventEntity
                }
            }
        }', $subscriber_id, $event_key, $caterer_id);

        return $queryString;
    }
}