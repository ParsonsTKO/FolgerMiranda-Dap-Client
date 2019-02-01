Feature: Homepage
  In order to validate the homepage
  As a visitor
  I need to be able to see the valid homepage

  Background: Check homepage
    Given I am on homepage
    Then the response status code should be 200
      And I should see "Folger Shakespeare Library - Digital Asset Platform"
