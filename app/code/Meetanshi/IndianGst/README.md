
================================================================
GraphQL
================================================================


================================================================
CHECK THE ORDER
================================================================

{
    customerCart {
        id
        items {
            id
            product {
                name
                sku
            }
            quantity
        }
        prices {
            discount {
                label
                amount {
                    value
                }
            }
            subtotal_excluding_tax {
                value
            }
            applied_taxes {
                label
                amount {
                    value
                }
            }
            grand_total {
                value
            }
            cgst {
                code
                title
                value
                currency
            }
            igst {
                code
                title
                value
                currency
            }
            sgst {
                code
                title
                value
                currency
            }
            utgst {
                code
                title
                value
                currency
            }
            shipping_cgst {
                code
                title
                value
                currency
            }
            shipping_igst {
                code
                title
                value
                currency
            }
            shipping_sgst {
                code
                title
                value
                currency
            }
            shipping_utgst {
                code
                title
                value
                currency
            }                                    
        }
    }
}


{
    "data": {
        "customerCart": {
            "id": "yWy9nLY6ZO7quNMdVAfDVJaHdGPdsEXR",
            "items": [
                {
                    "id": "74",
                    "product": {
                        "name": "Tshirt",
                        "sku": "Tshirt"
                    },
                    "quantity": 11
                }
            ],
            "prices": {
                "discount": null,
                "subtotal_excluding_tax": {
                    "value": 1100
                },
                "applied_taxes": [],
                "grand_total": {
                    "value": 1463
                },
                "cgst": [
                    {
                        "code": "cgst_amount",
                        "title": "CGST",
                        "value": 154,
                        "currency": "USD"
                    }
                ],
                "igst": [],
                "sgst": [
                    {
                        "code": "sgst_amount",
                        "title": "CGST",
                        "value": 154,
                        "currency": "USD"
                    }
                ],
                "utgst": [],
                "shipping_cgst": [
                    {
                        "code": "shipping_cgst_amount",
                        "title": "CGST",
                        "value": 6.02,
                        "currency": "USD"
                    }
                ],
                "shipping_igst": [],
                "shipping_sgst": [
                    {
                        "code": "shipping_sgst_amount",
                        "title": "CGST",
                        "value": 6.02,
                        "currency": "USD"
                    }
                ],
                "shipping_utgst": []
            }
        }
    }
}



================================================================