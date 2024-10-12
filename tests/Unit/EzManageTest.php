<?php

namespace Crazymeeks\Tests\Unit;

use Mockery;
use Crazymeeks\Tests\TestCase;
use Crazymeeks\PhpEzmanage\EzManage;
use Crazymeeks\PhpEzmanage\Http\Curl;
use Crazymeeks\PhpEzmanage\Http\Response;
use Crazymeeks\PhpEzmanage\Query\OrderQuery;

class EzManageTest extends TestCase
{

    private $curlMocked;
    private OrderQuery $orderQuery;

    public function setUp(): void
    {
        parent::setUp();

        $orderQuery = new OrderQuery();
        $orderUuid = "dafdaf";
        $orderQuery->setData($orderUuid);
        $this->orderQuery = $orderQuery;

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
             ->shouldReceive('with_data')
             ->with([
                'query' => $orderQuery->get()
             ])
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('as_json')
             ->andReturnSelf();
        $this->curlMocked
             ->shouldReceive('return_as_response_object')
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

        $ezManage = new EzManage($this->curlMocked);
        $ezManage->set_base_url('https://api.ezcater.com')
                 ->set_base_url_prefix('graphql')
                 ->set_authorization('the-token')
                 ->set_client_name('Client Name/Org')
                 ->set_client_version('1.0');

        $response = $ezManage->send_query($this->orderQuery);
        $this->assertInstanceOf(\Crazymeeks\PhpEzmanage\Http\Response::class, $response);

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