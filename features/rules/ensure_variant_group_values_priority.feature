@javascript
Feature: Ensure variant group values priority when execute a rule
  In order to ensure that values coming from variant group have higher priority than those coming from rules
  As a regular user
  I need to see variant group values when I execute rules on values coming from variant groups

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

    And the following product groups:
      | code  | label   | axis        | type    |
      | vboot | VG boot | size, color | VARIANT |
    And the following variant group values:
      | group | attribute | value                   | locale | scope |
      | vboot | name      | Name from variant group | en_US  |       |
    And the following product:
      | sku       | groups | color | size |
      | boot      | vboot  | black | 40   |
      | otherboot |        |       |      |
    And the following product values:
      | product | attribute | value                   | locale | scope |
      | boot    | name      | Name from variant group | en_US  |       |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value |
      | set_name | sku   | CONTAINS | boot  |
    And the following product rule setter actions:
      | rule     | field | value          | locale | scope |
      | set_name | name  | Name from rule | en_US  |       |

  Scenario: Successfully display values coming from variant group
    Given  the product rule "set_name" is executed
    When I am on the "boot" product page
    Then the product "boot" should have the following values:
      | name-en_US | Name from variant group |
    And I should see that Name is inherited from variant group attribute

  Scenario: Successfully display values coming from rules engine
    Given  the product rule "set_name" is executed
    When I am on the "otherboot" product page
    Then the product "otherboot" should have the following values:
      | name-en_US | Name from rule |
    And I should see that Name is a smart