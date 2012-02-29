Feature: test method
    In order to make it easy to run split tests
    As a developer
    I should be able to execute a test method 

Scenario: Test is created on first call
    Given the housey service has been bootstrapped
    When I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call get statistics
    Then I should see the following experiment statistics
        | Test name | Total Participants | Total Conversions |
        | dave123   | 1                  | 0                 | 

Scenario: Test participation is not recorded twice
    Given the housey service has been bootstrapped
    When I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call get statistics
    Then I should see the following experiment statistics
        | Test name | Total Participants | Total Conversions |
        | dave123   | 1                  | 0                 | 

Scenario: Test participation is recorded for different identities
    Given the housey service has been bootstrapped
    And I call set identity with "abcdef"
    When I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call set identity with "ghijkl"
    And I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call set identity with "mnopqr"
    And I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call set identity with "abcdef"
    When I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call get statistics
    Then I should see the following experiment statistics
        | Test name | Total Participants | Total Conversions |
        | dave123   | 3                  | 0                 | 
   
Scenario: Test is linked with specific conversion
    Given the housey service has been bootstrapped
    And I call set identity with "abcdef"
    When I call test with "dave123", conversion name "winner" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call bingo with "winner"
    And I call set identity with "mnopqr"
    And I call test with "dave123" and the following alternatives:
        | Content |
        | 1       |
        | 2       |
    And I call get statistics
    Then I should see the following experiment statistics
        | Test name | Total Participants | Total Conversions | Conversion Rate |
        | dave123   | 2                  | 1                 | 0.5             |
   

