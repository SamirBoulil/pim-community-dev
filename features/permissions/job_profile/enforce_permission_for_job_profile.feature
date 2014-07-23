@javascript
Feature: Define permissions for a job profile
  In order to be able to restrict access to job profiles
  As an administrator
  I need to be able to restrict access to job profiles

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully revoke access to execute a job
    Given I am on the "clothing_product_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Permissions to execute job profile | Redactor |
      | Permissions to edit job profile    | Redactor |
    When I save the job profile
    Then I should be on the "clothing_product_export" export job page
    And I should not see the "Export now" button
    And I should not be able to launch the "clothing_product_export" export job
    And I am on the exports page
    And I should not see export profiles clothing_product_export

  Scenario: Successfully revoke access to edit a job
    Given I am on the "clothing_product_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Permissions to edit job profile    | Redactor |
    When I save the job profile
    Then I should be on the "clothing_product_export" export job page
    And I should not see the "Edit" button
    And I should not be able to edit the "clothing_product_export" export job
