# FERN
FERN is an application designed to solve the Traveling Salesman problem for field data scientists. The user can load a previously created route that will be displayed on a map. The route consists of sequential points. The current point will be highlighted and its organism name displayed. The user can cycle through the points, and they can also search by area or plot for the organism names located within and display the results on a map. The application also features reporting and simple notes.

Traveling Salesman iOS App using a PostgreSQL backend and PHP API for the WGU Bachelor's Capstone. The iOS App can be found at https://github.com/exnehilo7/fielddata-lite-iOS-app.



This current version is for a school project.

Application Infrastructure
The Application has two major components: The iPad iOS 16.1 application itself and a PostgreSQL database backend on a Linux server with Apache and PHP. 

The iOS application project is located at https://github.com/exnehilo7/fielddata-lite-iOS-app and can be opened with XCode running a virtual device on a MacOS machine. For better performance, it is recommended to use one with at least an M2 chip. You can also tether a device to the MacOS machine. 
This iOS application is currently using a publicly hosted server. If you wish to set up a different or local server, you can follow the steps under the Backend section.

Apple Map performance declines if there are more than 100 displayed annotations. Later versions of the application will hopefully use clustering to allow for more annotations. Until then, the number of items in a saved route or area and plot searches are limited.


## Backend
If you wish to use your own hosted database, these are the steps to set up the backend on Linux.
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
apt install postgresql-[PostgresSQL'sVersion]-pgrouting
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
 - Enable the extansions in the database.
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
- After the canges are made, **Ctrl+S** then **Ctrl+X**.

## Creating a new Route
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
