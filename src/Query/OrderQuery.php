<?php

namespace Crazymeeks\PhpEzmanage\Query;

use Crazymeeks\PhpEzmanage\Contract\QueryInterface;
use Crazymeeks\PhpEzmanage\Query\BaseQuery;

class OrderQuery extends BaseQuery implements QueryInterface
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
        $uuid = $this->getData();

        $queryString = sprintf('
        query orderById {
            order(id: "%s") {
                    orderNumber
                    orderSourceType
                    isTaxExempt event {
                    headcount
                    timestamp
                    catererHandoffFoodTime orderType
                    thirdPartyDeliveryPartner
                }
                caterer {
                    uuid
                    live name
                    address {
                        street
                        city
                        state
                    }
                }
                totals {
                customerTotalDue {
                    currency
                    subunits
                }
                salesTax {
                    currency
                    subunits
                }
                    salesTaxRemittance {
                    currency subunits
                }
                subTotal {
                    currency
                    subunits
                }
                tip {
                    currency
                    subunits
                }
            }
            catererCart{
                totals {
                    catererTotalDue
                }
                feesAndDiscounts(type: DELIVERY_FEE) {
                    name
                    cost {
                        currency
                        subunits
                    }
                }
                orderItems{
                    name
                    uuid
                    totalInSubunits {
                        subunits
                        currency
                    }
                    menuId
                    posItemId
                    menuItemSizeId
                    quantity
                    noteToCaterer
                    specialInstructions
                        customizations{
                            customizationId
                            posCustomizationId
                            name
                            quantity
                            customizationTypeName
                        }
                    }
                }
            }
        }
        ', $uuid);

        return $queryString;
    }
}