# VesselAPI

VesselAPI is an API that imports a json data set to a MySQL database and handles GET requests with parameters. It receives GET requests and returns the results from the database as download attachments.


VesselAPI consists of 3 files:



* API.php : The API that handles requests

* database.php : A php script to initiate the database and import data from the ship_positions.json file to the MySQL database

* ship_positions.json : A file containing the data in json form



## Prerequisites

* [XAMPP](https://www.apachefriends.org/index.html) - A PHP web server solution stack package


### Installing


Download XAMPP and follow the installer's instructions.


After installing XAMPP, download the contents of this repository and move the VesselAPI folder in the following directory, where XAMPP is installed :

```
C:\xampp\htdocs\VesselAPI
```

Then open the XAMPP control panel and start Apache and MySQL to run the server.


## database.php


In order to create the database in MySQL, we need the database.php script and the ship_positions.json file in the same directory. 


Then run the database.php in a browser as follows :

```
http://localhost/VesselAPI/database.php
```


To see if the script was successfully executed, we can check if a newly created "vesselsdb" database exists in the administration view at :

```
http://localhost/phpmyadmin/
```

More information about the database "vesselsdb" can be viewed at the Database Schema section.


## API.php


Once the database has been created we can make requests, passing them as parameters like the following examples: 


### Example 1

```
http://localhost/VesselAPI/api.php/?mmsi=247039300&lonMin=10&latMax=41&timestampMin=1372683960&timestampMax=1372700340&contentType=csv
```


In this example we request fields in the database with the following filters 

* MMSI value is equal to 247039300
* Longitude value is greater than or equal to 10
* Latitude value is lesser than or equal 41
* Timestamp value is in range between 1372683960 and 1372700340
* Content type is csv


### Example 2
```
http://localhost/VesselAPI/api.php/?mmsi=247039300&lonMin=10&lonMax=17&latMin=30&latMax=41&timestampMin=1372683960&timestampMax=1372700340&contentType=vnd_api_json
```


In this example we request fields in the database with the following filters 

* MMSI value is equal to 247039300
* Longitude value is in range between 10 and 17
* Latitude value is in range between 30 and 41
* Timestamp value is in range between 1372683960 and 1372700340
* Content type is vnd.api+json


All results are returned as download attachments in the requested content type. If a content type is not provided as a parameter, no results are returned.


More information about content types can be viewed at the Parameters section.


## Database Schema


The database "vesselsdb" consists of the following tables and fields: 


* vessels
  * id : INT NOT NULL PRIMARY KEY AUTO_INCREMENT
  * mmsi : INT NOT NULL
  * status : INT NOT NULL
  * stationId : INT NOT NULL
  * speed : INT NOT NULL
  * lon : INT NOT NULL
  * lat : INT NOT NULL
  * course : INT NOT NULL
  * heading : INT NOT NULL
  * rot : INT NOT NULL
  * timestamp : INT NOT NULL
* users
  * id : INT NOT NULL PRIMARY KEY AUTO_INCREMENT
  * ip : VARCHAR(30) NOT NULL
  * userRequests : INT NOT NULL
  * firstTimestamp : INT NOT NULL
  * lastTimestamp : INT NOT NULL


## Parameters


Parameters are accepted as follows :

* MMSI: ?mmsi=247039300 
  
  Only single is accepted, multiple filters don't work properly.

* LONGITUDE:
  * Longitude greater than a value: ?lonMin=10
  * Longitude lesser than a value: ?lonMax=17
  * Longitude range: ?lonMin=10&lonMax=17

* LATITUDE:
  * Latitude greater than a value: ?latMin=30
  * Latitude lesser than a value: ?latMax=41
  * Latitude range: ?latMin=30&latMax=41

* TIMESTAMP
  * Timestamp greater than a value: ?timestampMin=1372683960
  * Timestamp lesser than a value: ?timestampMax=1372700340
  * Timestamp range: ?timestampMin=1372683960&timestampMax=1372700340


* CONTENTTYPE
  * application/json : ?contentType=json
  * application/xml : ?contentType=xml
  * text/csv : ?contentType=csv
  * application/vnd.api+json : ?contentType=vnd_api_json



## Requests per User

Users are limited to 10 requests per hour.

When a user makes a request, his IP is tracked and saved in the database, along with request activity information, which consists of the number of requests made, the timestamp of his first request and the timestamp of his last request.

If a user has made more than 10 requests, he has to wait 1 hour to submit another request. After 1 hour his requests are reset to 0.

## Request logging


All requests are saved in a log file named requestslog.txt. The information saved are the following: 

* Host
* User-Agent
* Accept
* Accept-Language
* Accept-Encoding
* DNT
* Connection
* Cookie
* Upgrade-Insecure-Requests
* Cache-Control
* Request Method
* Request URI
* Ip Address
