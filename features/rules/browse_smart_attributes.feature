@javascript
Feature: Browse smart attributes in the attribute grid
  In order to know which attributes are smart
  As a regular user
  I need to see and filter by the smart property

  Background:
    Given a "footwear" catalog configuration
    And the following product rules:
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value |
      | rule1 | sku   | =        | foo   |
    And the following product rule setter actions:
      | rule  | field | value | locale |
      | rule1 | name  | Foo   | en_US  |
    And I am logged in as "Julia"

  Scenario: Successfully display the smart column in the attribute grid
    Given I am on the attributes page
    Then I should see the columns Code, Label, Type, Scopable, Localizable, Group and Smart

  Scenario: Successfully filter by the smart property in the attribute grid
    Given I am on the attributes page
    When I filter by "Type" with value "Text"
    Then I should be able to use the following filters:
      | filter | value | result  |
      | Smart  | yes   | name    |
      | Smart  | no    | comment |