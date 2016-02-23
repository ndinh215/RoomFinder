# features/offer.finder.feature
Feature: Offer finder
  In order to find room names at the hotel "The Reverie Residence" at a specific date
  As anybody
  I need to provide a specific date

Scenario: I can find room names at "The Reverie Residence"
  Given I search for room names available at "2017-02-27"
  When I send a "POST" request
  Then I should see "1 Bedroom Classic"