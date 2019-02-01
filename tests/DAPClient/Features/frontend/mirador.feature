Feature: Mirador
  In order to validate the Mirador page
  As a visitor
  I need to be able to see the valid Mirador page

  Scenario: See page not found when using invalid DAPId
    Given I go to "/mirador/34uiu3"
    Then the response status code should be 404
    And  I should see "Page not found"
    
  Scenario: See Mirador page
    Given I am on "/detail/oeuvres-choisies-de-shakespeare/bc3728bc-3ef0-4a29-be54-0736cda1aa0b"
    When I follow "OPEN IN MIRADOR"
      Then the response status code should be 200
      And I should see "Mirador - Folger Shakespeare Library - Digital Asset Platform"    
