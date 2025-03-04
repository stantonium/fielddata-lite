# FERN
FERN (Field Expedition Routing and Navigation) is an application designed to solve the Traveling Salesman problem for field data scientists. The Application has two major components: The iPad iOS application itself and a PostgreSQL database backend on a Linux server with Apache and PHP. The iOS App can be found at https://github.com/exnehilo7/fielddata-lite-iOS-app.

This application and backend was originally created for a school project.

Note: As of 12-SEP-2024, the functions in the database schema file are outdated. Updated schema file is pending.


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

## PHP.INI file
In order to upload images, the upload_max_filesize directive should be increased from the default of 2M (for php 7.4) to 10MB or larger.
Find the php.ini file for your php installation and change **upload_max_filesize**'s value.

## Creating a New Route
A route can be created by uploading a CSV via the HTML file. Expected columns are currently set to match an export from a different project.
