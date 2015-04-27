Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku        | name-en_US | description-en_US-mobile | description-en_US-tablet | price-EUR | price-USD | categories  | legacy_attribute |
      | sandals    | My sandals | My great sandals         | My great new sandals     | 20        | 30        | sandals     | old value        |
      | oldsandals | My sandals | My great sandals         | My great new sandals     | 20        | 30        | old_sandals | old value        |

  @skip
  Scenario: Successfully retrieve a product by applying permissions on attribute groups
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I request information for product "sandals"
    Then the response code should be 200
    And the response should be valid json
    And the response should contain json:
    """
    {
      "family":null,
      "groups":[],
      "categories":["sandals"],
      "enabled":true,
      "associations":[],
      "values": {
        "sku":[
          {"locale":null,"scope":null,"value":"sandals"}
        ],
        "name":[
          {"locale":"en_US","scope":null,"value":"My sandals"},
          {"locale":"de_DE","scope":null,"value":null},
          {"locale":"fr_FR","scope":null,"value":null}
        ],
        "description":[
          {"locale":"en_US","scope":"mobile","value":"My great sandals"},
          {"locale":"en_US","scope":"tablet","value":"My great new sandals"},
          {"locale":"de_DE","scope":"mobile","value":null},
          {"locale":"fr_FR","scope":"mobile","value":null},
          {"locale":"de_DE","scope":"tablet","value":null},
          {"locale":"fr_FR","scope":"tablet","value":null}
        ],
        "price":[
          {"locale":null,"scope":null,"value":[
            {"data":"20.00","currency":"EUR"},
            {"data":"30.00","currency":"USD"}
          ]}
        ]
      },
      "resource":"{baseUrl}/api/rest/products/sandals"
    }
    """

  @skip
  Scenario: Fail to fetch a not granted product by applying permissions on categories
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I request information for product "oldsandals"
    Then the response code should be 403