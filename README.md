# FERN
FERN (Field Expedition Routing and Navigation) is an application designed to solve the Traveling Salesman problem for field data scientists. The Application has two major components: The iPad iOS 16.1 application itself and a PostgreSQL database backend on a Linux server with Apache and PHP. The iOS App can be found at https://github.com/exnehilo7/fielddata-lite-iOS-app.

This application is currently built for a school project.


## Backend
If you wish to use your own hosted database:

Start as Linux’s root user.

1.	Set Up Apache and PHP.
```
apt-get install apache2
apt-get install php
apt-get install php-pgsql (or: apt-get install php[version]-pgsql)
```
2.	Install PostgreSQL and its extensions.
```
apt-get install postgresql
apt-get install postgis
apt-get install postgresql-[PostgresSQL'sVersion]-pgrouting
```
3.	Add Git (If necessary).
```
apt-get install git
```
4.	Start the Services.
```
apachectl start
service postgresql start
```
5.	Clone the repo.
```
cd /var/www/html
git clone https://github.com/exnehilo7/fielddata-lite.git
```
6.	Create the database.
 - Log into psql as the postgres user.
 ```
 su postgres
 psql
 ```
 - Change the postgres’ user password to something other than its default value. DO NOT LOSE THE PASSWORD.
 ```
 \password postgres
 ```
 - Create and connect to a database named “fielddata_lite”.
 ```
 create database fielddata_lite;
 \c fielddata_lite
 ```
 - Create a user named “routes”.
 ```
 create user routes;
 alter user routes with password ‘PasswordOfYourChoice’;
 alter role routes NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT LOGIN;
 ```
 - Enable the extensions in the database.
 ```
 CREATE EXTENSION IF NOT EXISTS postgis;
 CREATE EXTENSION IF NOT EXISTS pgrouting;
 ```
 - Add data using a dump file.
 ```
 \i /var/www/html/fielddata-lite/SQL/dump/dump-fielddata_lite.sql
 ```
 - Update the rights for routes.
  ```
  \c
  \i /var/www/html/fielddata-lite/SQL/dump/user-rights.sql
  ```
7.	Move and Update the fielddata.env File
 - Exit psql and switch back to the root user
 ```
 \q
 exit
 ```
 - Move the fielddata.env File and update its values. Be sure that the user name, user password, and database name matches the values that you set up in previous steps.
 ```
 mv /var/www/html/fielddata-lite/fielddata.env /var/www/.
 nano /var/www/fielddata.env
 ```
- After the changes are made, **Ctrl+S** then **Ctrl+X**.

## Creating a New Route
Planned improvements to the application will allow the user to create their own route.  Until then, a database administrator can create a route with a CSV file supplied by the user. The CSV file is a list of selected IDs from the bsd_site table and their matching organism names from the bsd_tree table (See the ERD file for table relationships). The user will also have to provide the name of the route they wish to create and the organism name they want for the starting point of their route.

NOTE – Due to the hardware limitations of Apple Map and the application’s current version, routes will have to be limited to 100 items or less. For field expeditions that require more than 100 visited points, it is recommended to create several smaller routes.

1.	Using a Database Management System of your choice, clear out data from the **temp_selected_bsd_site_ids** table.
```
delete from temp_selected_bsd_site_ids;
```
2.	Import the CSV into the **temp_selected_bsd_site_ids** table.
3.	Get the ID of the starting point.
```
select bs.id, organism_name  
from temp_selected_bsd_site_ids tsbsi
join bsd_site bs on bs.id = tsbsi.id
where organism_name = '[name]';
```
4.	Create the base route data. Pass the chosen route's name and the ID of the user to whom the route will belong. A successful function call will return 1.
Note: Until user login is implemented into the app, there is only one user.
```
select * from query_route_create_user_base_route('[NameOfRoute]', 
(select user_id from lookup_users where name = 'Field Data'));
```
5.	Create the route for display in the app. Pass the ID of the route name and the starting point's ID. A successful function call will return 1.
```
select * from query_route_main(
    (select id from lookup_routes where "name" = '[NameOfRoute]'), 
    [StartingPontID]);
```
6.	The user should now be able to see their route listed within the application’s Saved Routes section.
