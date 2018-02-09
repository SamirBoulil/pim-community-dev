@javascript
Feature: Export product models according to text attribute filter
  In order to export specific product models
  As a product manager
  I need to be able to export the product models according to text attribute values

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Export products by text values
    When I am on the "csv_product_model_export" export job edit page
    And I visit the "Content" tab
    # And I add available attributes Comment
    And I add available attributes Model name
    And I switch the locale from "name" filter to "en_US"
    # And I filter by "comment" with operator "Is equal to" and value "Awesome"
    And I filter by "name" with operator "Contains" and value "Bag"
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_product_model_export" export job page
    And I launch the export job
    And I wait for the "csv_product_model_export" job to finish
    Then exported file of "csv_product_model_export" should contain:
    """
    sku;categories;enabled;family;groups;comment;name-en_US;title;title_2;title_3
    SNKRS-1B;summer_collection;1;rangers;;Awesome;"Ranger 1B";"My title";"Awesome title";
    """
