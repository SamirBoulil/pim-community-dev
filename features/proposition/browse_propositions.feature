@javascript
Feature: Browse propositions for a specific product
  In order to list the existing propositions for a specific product
  As a product manager
  I need to be able to see propositions

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following propositions:
      | product     | status      | author |
      | black-boots | in progress | Julia  |
      | black-boots | ready       | Mary   |
      | white-boots | ready       | Sandra |
    And I am logged in as "Julia"

  Scenario: Successfully display propositions
    Given I edit the "black-boots" product
    When I visit the "Propositions" tab
    Then the grid should contain 2 elements
    And I should see the columns Author, Changes, Proposed at and Status
    And I should see entities Julia and Mary
