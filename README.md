Room finder
=======

This project supports adding & removing any offer and rooms related from the hotel Riverside, HCM, Vietnam in the site hotels.com, at a specific date.

It applied these followings:
- Symfony 
- Behat 
- Doctrine ORM 
- PHPUnit 


Install the project
=========
1. Edit the MySQL config in the file `/app/config/parameters.yml`.
2. Run the command `php bin/console doctrine:database:create` to create the database.
3. Run the command `php bin/console doctrine:schema:update --force` to update the database schema.
4. Run the command `php bin/console server:run` to start the built-in server. Now the project is running.

The features of the project
========

The project provides 2 APIs:

1. `/api/offers/{date}`: gets and saves room names at a specific date.
2. `/api/offers/{id}`: removes an offer with a specific Id.



