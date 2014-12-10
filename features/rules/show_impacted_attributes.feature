@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which attributes are affected or not
  As a regular user
  I need to see which attributes are affected by a rule or not

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"

  Scenario: Successfully create, edit and save a product
    Given the following products:
      | sku       | family  |
      | my-loafer | sandals |
    And the following product rules:
      | code      | priority |
      | copy_rule | 10       |
    And the following product rule conditions:
      | rule      | field | operator | value     |
      | copy_rule | sku   | =        | my-loafer |
    And the following product rule setter actions:
      | rule      | field | value     | locale |
      | copy_rule | name  | My loafer | en_US  |
    And the following product rule copier actions:
      | rule      | from_field  | to_field | to_locale | from_scope | to_scope | from_locale | to_locale |
      | copy_rule | description | name     | en_US     |            |          |             |           |
    Then I am on the "my-loafer" product page
    And I should see that Name is a smart
    Then I should see the smart attribute tooltip
