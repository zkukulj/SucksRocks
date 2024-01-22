SucksRocks is an API project created in Symfony (PHP 8.*) 

Its purpose is to allow searching GitHub issues (https://docs.github.com/en/rest/reference/search#search-issues-and-pull-requests) by keyword and calculates the popularity of a certain word.

{word} rocks as a positive result and {word} sucks as a negative. The result is a popularity rating of the given word from 0-10 as a ratio of positive results to the total number of results. 

The results is saved in local database (sqlite) to make future queries for the same words faster. 

It is expected in the future adding/changing providers (currently Twitter and GitHub defaults to Github if omitted)

To install clone the repository using "https://github.com/zkukulj/SucksRocks.git"
- make sure you have Composer installed
- run Composer update
- start symfony server and navigate to default url
- "score?term=php&from=GitHub" term=word, from is an API instance, change appropriately

To continue working on API expansion and add other providers to ApiLinks class based on new providers API description
