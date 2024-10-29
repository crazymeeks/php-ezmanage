<?php

namespace Crazymeeks\Tests\Unit;

use Mockery;
use Crazymeeks\Tests\TestCase;
use Crazymeeks\PhpEzmanage\EzManage;
use Crazymeeks\PhpEzmanage\Http\Curl;
use Crazymeeks\PhpEzmanage\Http\Response;
use Crazymeeks\PhpEzmanage\Query\OrderQuery;
use Crazymeeks\PhpEzmanage\Query\AllCatererQuery;
use Crazymeeks\PhpEzmanage\Query\Mutation\EventSubscriberQuery;
use Crazymeeks\PhpEzmanage\Query\Mutation\SetupSubscription;

class EzManageTest extends TestCase
{

    private $curlMocked;
    private OrderQuery $orderQuery;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderQuery = new OrderQuery();
        $orderUuid = "dafdaf";
        $this->orderQuery->setData($orderUuid);
        $this->orderQuery = $this->orderQuery;

        $this->curlMocked = Mockery::mock(Curl::class);

        $this->curlMocked
             ->shouldReceive('to')
             ->with('https://api.ezcater.com/graphql')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('with_headers')
             ->with([
                'Authorization' => 'the-token',
                'Apollographql-client-name' => 'Client Name/Org',
                'Apollographql-client-version' => '1.0'
             ])
             ->andReturnSelf();

        $this->curlMocked
             ->shouldReceive('as_json')
             ->andReturnSelf();
        
        
    }

    private function mockedForOrderQuery()
    {
        
        $this->curlMocked
             ->shouldReceive('return_as_response_object')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('with_data')
             ->with([
                'query' => $this->orderQuery->get()
             ])
             ->andReturnSelf();
        
        $this->curlMocked
             ->shouldReceive('debug')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('post')
             ->andReturn(
                new Response([
                    'message' => 'Success',
                    'statusCode' => 200,
                    'data' => $this->getResponse()
                ])
             );
    }

    public function test_ezmanage_query_order_by_uuid()
    {
        $this->mockedForOrderQuery();
        $configs = [
            'base_url' => 'https://api.ezcater.com',
            'base_url_prefix' => 'graphql',
            'authorization' => 'the-token',
            'client_name' => 'Client Name/Org',
            'client_version' => '1.0'
        ];


        $ezManage = new EzManage($this->curlMocked, $configs);

        $response = $ezManage->as_json()
                             ->debug()
                             ->return_as_response_object()
                             ->send_query($this->orderQuery);
        
        $this->assertInstanceOf(\Crazymeeks\PhpEzmanage\Http\Response::class, $response);

    }

    public function test_ezmanage_config_setter()
    {

        $this->mockedForOrderQuery();

        $ezManage = new EzManage($this->curlMocked);
        $ezManage->set_base_url('https://api.ezcater.com')
                 ->set_base_url_prefix('graphql')
                 ->set_authorization('the-token')
                 ->set_client_name('Client Name/Org')
                 ->set_client_version('1.0');

        $response = $ezManage->send_query($this->orderQuery);
        $this->assertInstanceOf(\Crazymeeks\PhpEzmanage\Http\Response::class, $response);

    }

    public function test_create_subscriber()
    {
    
        $subscriberQuery = new EventSubscriberQuery();
        $subscriberQuery->setData('http://your-webhook-url.com/your-desired-endpoint');
        
        $this->curlMocked
             ->shouldReceive('with_data')
             ->with([
                'query' => $subscriberQuery->get()
             ])
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('return_as_array')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('debug')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('post')
             ->andReturn(
                new Response([
                    'message' => 'Success',
                    'statusCode' => 200,
                    'data' => $this->createSubscriberResponse()
                ])
             );

        
        
        $ezManage = new EzManage($this->curlMocked);
        $ezManage->set_base_url('https://api.ezcater.com')
                 ->set_base_url_prefix('graphql')
                 ->set_authorization('the-token')
                 ->set_client_name('Client Name/Org')
                 ->set_client_version('1.0')
                 ->as_json();

        $response = $ezManage->send_query($subscriberQuery);

        $this->assertSame($response->data, $this->createSubscriberResponse());
    }

    public function test_query_all_caterers()
    {
    
        $catererQuery = new AllCatererQuery();

        $this->curlMocked
             ->shouldReceive('with_data')
             ->with([
                'query' => $catererQuery->get()
             ])
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('return_as_array')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('debug')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('post')
             ->andReturn(
                new Response([
                    'message' => 'Success',
                    'statusCode' => 200,
                    'data' => $this->queryAllSubscriberResponse()
                ])
             );
        
        $ezManage = new EzManage();
        $ezManage->set_base_url('https://api.ezcater.com')
                 ->set_base_url_prefix('graphql')
                 ->set_authorization('the-token')
                 ->set_client_name('Client Name/Org')
                 ->set_client_version('1.0')
                 ->as_json();

        $response = $ezManage->send_query($catererQuery);
        
        $this->assertSame($response->data, $this->queryAllSubscriberResponse());
    }

    /**
     * Create order subscription for accepted order
     */
    public function test_create_subscription()
    {
    
        $setupSubscriptionQuery = new SetupSubscription();

        $setupSubscriptionQuery->setData([
            'subscriber_id' => 'subscriber_id',
            'event_key' => 'accepted',
            'caterer_id' => 'caterer_id',
        ]);

        $this->curlMocked
             ->shouldReceive('with_data')
             ->with([
                'query' => $setupSubscriptionQuery->get()
             ])
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('return_as_array')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('debug')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('post')
             ->andReturn(
                new Response([
                    'message' => 'Success',
                    'statusCode' => 200,
                    'data' => null
                ])
             );
        
        $ezManage = new EzManage($this->curlMocked);
        $ezManage->set_base_url('https://api.ezcater.com')
                 ->set_base_url_prefix('graphql')
                 ->set_authorization('the-token')
                 ->set_client_name('Client Name/Org')
                 ->set_client_version('1.0')
                 ->as_json();

        $response = $ezManage->send_query($setupSubscriptionQuery);
        
        $this->assertSame($response->data, null);
    }

    private function queryAllSubscriberResponse()
    {
        $data = [
            'data' => [
                'caterers' => [
                    [
                        'uuid' => '61d78e42-97a6-4f4f-905b',
                        'name' => "Ez store bbq",
                        'storeNumber' => '',
                        'live' => true,
                        'address' => [
                            'street' => '1600 Charleston Hwy',
                            'street2' => null,
                            'state' => 'SC',
                            'stateName' => 'South Carolina',
                            'city' => 'West Columbia',
                            'zip' => '29169'
                        ]
                    ],
                    [
                        'uuid' => 'b10934dd-2adb-462c-9d21',
                        'name' => "Ez store bbq",
                        'storeNumber' => '',
                        'live' => true,
                        'address' => [
                            'street' => '4411 Devine St',
                            'street2' => null,
                            'state' => 'SC',
                            'stateName' => 'South Carolina',
                            'city' => 'Columbia',
                            'zip' => '29205'
                        ]
                    ],
                    [
                        'uuid' => '84ebf066-23c0-46c9-a359',
                        'name' => "Ez store bbq",
                        'storeNumber' => '',
                        'live' => true,
                        'address' => [
                            'street' => '107 Clemson Rd',
                            'street2' => null,
                            'state' => 'SC',
                            'stateName' => 'South Carolina',
                            'city' => 'Columbia',
                            'zip' => '29229'
                        ]
                    ]
                ]
            ]
        ];
        return $data;
    }

    private function createSubscriberResponse()
    {

        $data = [
            'data' => [
                'createSubscriber' => [
                    'subscriber' => [
                        'id' => '0fc73833-fac0-4a4b-be38-b0a9baea4507',
                        'name' => 'Flex Api Integration',
                        'webhookUrl' => 'http://your-webhook-url.com/your-desired-endpoint',
                        'webhookSecret' => 'A9e8018dac74463c632dea294494ca468f470c4df8513309d0e7ef8e265e08f7',
                    ],
                ],
            ],
        ];
        return $data;
    }

    private function getResponse()
    {
        return '{
        "data": {
            "order": {
            "orderNumber": "444443",
            "orderSourceType": "MARKETPLACE",
            "isTaxExempt": false,
            "event": {
                "headcount": 16,
                "timestamp": "2024-10-10T15:30:00Z",
                "catererHandoffFoodTime": "2024-10-10T15:15:00Z",
                "orderType": "DELIVERY",
                "thirdPartyDeliveryPartner": null
            },
            "caterer": {
                "uuid": "dklafdae-3439043ledkfldf",
                "live": true,
                "name": "Caterer name",
                "address": {
                "street": "Street here",
                "city": "City here",
                "state": "SC"
                }
            },
            "totals": {
                "customerTotalDue": {
                "currency": "USD",
                "subunits": 232
                },
                "salesTax": {
                "currency": "USD",
                "subunits": 333
                },
                "salesTaxRemittance": {
                "currency": "USD",
                "subunits": -4555
                },
                "subTotal": {
                "currency": "USD",
                "subunits": 23434
                },
                "tip": {
                "currency": "USD",
                "subunits": 345
                }
            },
            "catererCart": {
                "totals": {
                "catererTotalDue": 305.21
                },
                "feesAndDiscounts": [
                {
                    "name": "Delivery Fee",
                    "cost": {
                    "currency": "USD",
                    "subunits": 4545
                    }
                }
                ],
                "orderItems": [
                {
                    "name": "Mcdo",
                    "uuid": "349304-34930493-dadafd",
                    "totalInSubunits": {
                    "subunits": 45454,
                    "currency": "USD"
                    },
                    "menuId": "3493049304-3403434903",
                    "posItemId": "",
                    "menuItemSizeId": "dlafj30493043ld0-34343ldflkadkfa",
                    "quantity": 10,
                    "noteToCaterer": null,
                    "specialInstructions": null,
                    "customizations": []
                },
                {
                    "name": "Chicken Spaghetti",
                    "uuid": "dlafj30493043ld0-34343ldflkadkfa",
                    "totalInSubunits": {
                    "subunits": 545454,
                    "currency": "USD"
                    },
                    "menuId": "dlafj30493043ld0-34343ldflkadkfa",
                    "posItemId": null,
                    "menuItemSizeId": "dlafj30493043ld0-34343ldflkadkfa",
                    "quantity": 5,
                    "noteToCaterer": null,
                    "specialInstructions": null,
                    "customizations": []
                },
                {
                    "name": "Fish Fillet",
                    "uuid": "dlafj30493043ld0-34343ldflkadkfa",
                    "totalInSubunits": {
                    "subunits": 499,
                    "currency": "USD"
                    },
                    "menuId": "dlafj30493043ld0-34343ldflkadkfa",
                    "posItemId": "",
                    "menuItemSizeId": "dlafj30493043ld0-34343ldflkadkfa",
                    "quantity": 1,
                    "noteToCaterer": null,
                    "specialInstructions": null,
                    "customizations": []
                }
                ]
            }
            }
        }
        }';
    }
}