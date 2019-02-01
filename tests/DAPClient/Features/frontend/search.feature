Feature: Search
  In order to see search results
  As a visitor
  I need to be able to enter search terms and see content results

  Scenario: Check search autofill of filters after search
    Given I am on homepage
      And I select "Printed Text" from "format"
      And I fill in "*" for "Enter your search"
      And I select "English" from "Language"
      And I select "Performance Materials" from "Genre"
      And I fill in "1300" for "Created from"
      And I fill in "2018" for "Created until"
      And I check "Only show media available online"
    When I press "Search"
    Then I should see "Your current search for “*”"
      And the "Enter your search" field should contain "*"
      And the "English" option from "Language" should be selected
      And the "Printed Text" option from "format" should be selected
      And the "Performance Materials" option from "Genre" should be selected
      And the "Only show media available online" checkbox should be checked
      And the "Created from" field should contain "1300"
      And the "Created until" field should contain "2018"
      And I should see "Records" in the "Search results"

  Scenario: See detail view of record from home tiles: See a bracelet of Edwin Booth's hair
    Given I am on "/detail/oeuvres-choisies-de-shakespeare/bc3728bc-3ef0-4a29-be54-0736cda1aa0b"
    Then the response status code should be 200
      And  I should see "Oeuvres Choisies de Shakespeare"
      And I should see "Media format"
      And I should see "OPEN IN MIRADOR"
      And I should see "DOWNLOAD METADATA"

  Scenario: Se valid Downlaod CSV file
    Given I am on "/detail/oeuvres-choisies-de-shakespeare/bc3728bc-3ef0-4a29-be54-0736cda1aa0b"
    When I follow "DOWNLOAD METADATA"
    Then the response status code should be 200

  # Pagination tests can't be successfull without enough test data. Try to reduce pagination (3 item per page for)
  Scenario: Check search with pagination - next page
    Given I am on homepage
      And I fill in "*" for "Enter your search"
    When I press "Search"
    Then I should see "Results"
      And I should see "Records" in the "Search results"
      And I should see "Pagination" in the "Search results"
    When I follow "next"
    Then I should see "Results"
      And I should see "Records" in the "Search results"
      And I should see "Pagination" in the "Search results"
    When I follow "prev"
    Then I should see "Results"
      And I should see "Records" in the "Search results"
      And I should see "Pagination" in the "Search results"
