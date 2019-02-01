Feature: MyShelf
  In order to validate the MyShelf
  As a user
  I need to be able to interact with MyShelf

  Background: Login as User
    Given I am on "login-check?api-key=5b541ee9-dd64-4803-af6d-dfb4bc1fcc2c"
      And I follow "MY SHELF"

  Scenario: Start with an empty Shelf
    Given I should see "My Shelf"    
    When I follow "CLEAR"
    Then I should be on "myshelf/"
      And I should see "Your shelf is empty."  

  Scenario: Add item to MyShelf
    Given I am on the homepage
      And I fill in "\"Metamorphoses. French (Middle French)\"" for "Enter your search"
    When I press "Search"
      And I follow "Metamorphoses. French (Middle French)"      
      And I follow "ADD TO SHELF"
      And I follow "MY SHELF"
    Then I should see "Metamorphoses. French (Middle French)"

  Scenario: Create new folder in MyShelf
    Given I fill in "New folder Behat" for "New Folder Name"
    When I press "CREATE"
    Then I should see "New folder Behat (0)"
    When I follow "New folder Behat (0)"
    Then I should see "New folder Behat (0)"
      And I should see "Folder empty."

  Scenario: Rename folder in MyShelf
    When I follow "New folder Behat (0)"
    Given I fill in "New folder Behat Renamed" for "New folder Behat"
    When I press "SaveRenameFolder"
    Then I should see "New folder Behat Renamed (0)"
      And I should see "Folder empty."

  Scenario: Add item to folder
    Given I follow "New folder Behat Renamed"
    Then I should be on "myshelf/"
    And I should see "New folder Behat Renamed (1)"  
    And I should not see "Metamorphoses. French (Middle French)" 

  Scenario: See folder content
    Given I follow "New folder Behat Renamed (1)"
    Then I should see "Metamorphoses. French (Middle French)" 

  Scenario: Move item to other Folder
    Given I fill in "Second folder Behat" for "New Folder Name"
      And I press "CREATE"
      And I follow "New folder Behat Renamed (1)"
    When I follow "Second folder Behat"
    Then I should see "New folder Behat Renamed (0)"
      And I should see "Folder empty."
    When I follow "My Shelf"
    Then I should see "New folder Behat Renamed (0)"
      And I should see "Second folder Behat (1)"
    When I follow "Second folder Behat (1)"
    Then I should see "Second folder Behat (1)"
      And I should not see "Folder empty."
    Then I should see "Metamorphoses. French (Middle French)"         

  Scenario: Share folder
    Given I follow "Second folder Behat (1)"
    When I follow "SHARE FOLDER"
    Then I should see "This is a public folder, you can share it with this link:"
    When I follow "Shared folder link"  
    Then I should see "'s Shelf"
      And I should see "Second folder Behat (1)"

  Scenario: Unshare folder
    Given I follow "Second folder Behat (1)"
    When I should see "This is a public folder, you can share it with this link:"    
    Then I follow "UNSHARE FOLDER"
      And I should not see "This is a public folder, you can share it with this link:"

  Scenario: Remove folder
    Given I follow "New folder Behat Renamed (0)"
    When I press "REMOVE"
    Then I should be on "myshelf/"
      And I should not see "New folder Behat Renamed (0)"   

  Scenario: Clear Folder  
    Given I follow "Second folder Behat (1)"
      When I follow "CLEAR"
    Then I should see "Second folder Behat (0)"
      And I should see "Folder empty."
      And I should not see "Metamorphoses. French (Middle French)" 
    When I follow "My Shelf"
    Then I should see "Second folder Behat (0)"
      And I should not see "Metamorphoses. French (Middle French)"    

  Scenario: Clear MyShelf  
    Given I follow "CLEAR"
    Then I should be on "myshelf/"
      And I should see "Your shelf is empty."
      And I should not see "Second folder Behat (0)"      