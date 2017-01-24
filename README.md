# YoukeBox-server-PHP
This is the befamed YoukeBox backend, written in PHP

## Installation
Currently, the only database supported is sql

### Create an SQL user and DB:
CREATE DATABASE youkebox;
CREATE USER 'youkebox_user'@'localhost' IDENTIFIED BY 'YourSecretPassword';
GRANT ALL PRIVILEGES ON youkebox.* TO 'youkebox_user'@'localhost';

Set the password in includes/db_connection.php

Then execute the youkebox.sql file
