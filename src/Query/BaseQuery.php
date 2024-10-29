<?php

namespace Crazymeeks\PhpEzmanage\Query;

abstract class BaseQuery
{

    /**
     * @var mixed
     */
    protected $data = [];


    /**
     * Mutation createSubscription validator
     * 
     * @return array
     * 
     * @throws \LogicException
     */
    protected function validate_create_subscription_query()
    {
        $data = $this->getData();

        if (!array_key_exists('subscriber_id', $data)
            || !array_key_exists('event_key', $data)
            || !array_key_exists('caterer_id', $data)) {
            throw new \LogicException("Unable to execute mutation createSubscription. Make sure you passed subscriber_id, event_key and caterer_id.");
        }

        [
            'subscriber_id' => $subscriber_id,
            'event_key' => $event_key,
            'caterer_id' => $caterer_id
        ] = $data;
        
        if (empty($subscriber_id)) {
            throw new \LogicException("subscriber_id is required.");
        }

        if (empty($event_key)) {
            throw new \LogicException("event_key is required.");
        }

        if (empty($caterer_id)) {
            throw new \LogicException("caterer_id is required.");
        }

        // only cancelled and accepted allowed
        if (!in_array(strtolower($event_key), ['accepted', 'cancelled'])) {
            throw new \LogicException("EzCater only supports Order event 'accepted' and 'cancelled' as of the moment.");
        }

        return $data;

    }

    /** @inheritDoc */
    public function getData()
    {
        return [];
    }
}