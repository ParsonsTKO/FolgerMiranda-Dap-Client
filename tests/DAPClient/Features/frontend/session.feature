Feature: Session using API
  In order to log in
  As a visitor
  I need to be able to see the valid homepage

Background:
  Given I am on homepage
  Then I should see "Log in"
    And I should see "Sign Up"

  Scenario: Login user
    Given I follow "Log in"
    Then I should be on "/login"

  Scenario: Register user
    Given I follow "Sign Up"
    Then I should be on "/register/"

  Scenario: Login and logout using API Callback
    Given I go to "/login-check?api-key=7aabfb4f-ede7-48ef-a600-8a58c59dab1f&email=test@test.com&displayname=&username=test"
    Then I should be on homepage
      And I should see "test"
    When I follow "Log out"
    Then I should be on homepage
      And I should see "Log in"
      And I should see "Sign Up"

  Scenario: Login using API Callback wihtout Display name and fallback to username
    Given I go to "/login-check?api-key=7aabfb4f-ede7-48ef-a600-8a58c59dab1f&email=test@test.com&displayname=&username=test"
    Then I should be on homepage
      And I should see "test"

  Scenario: Login using API Callback wihtout Display name and username
    Given I go to "/login-check?api-key=7aabfb4f-ede7-48ef-a600-8a58c59dab1f&email=test@test.com&displayname=&username="
    Then I should be on homepage
      And I should see "test"
