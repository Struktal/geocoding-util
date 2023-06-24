# PHP-Geocoding
This is a simple PHP library to (reverse-)geocode addresses with the Nominatim API.

> <b>Legal Note:</b> This library uses the [Nominatim](https://nominatim.org/) API. Please read the [Terms of Use](https://operations.osmfoundation.org/policies/nominatim/) before using it and comply with them.

## Installation
To install this library, include it in your project using composer:
```json
{
    "require": {
        "jensostertag/php-geocoding": "dev-main"
    }
}
```

## Usage
<details>
<summary><b>Geocode an address to coordinates</b></summary>

The following example shows how to geocode an address to coordinates:
```php
<?php

use jensostertag\Geocoding\Geocoding;

$geocoding = new Geocoding();
$geocoding->setStreet("James-Franck-Ring")
          ->setHouseNumber("1")
          ->setCity("Ulm")
          ->setZipCode("89081")
          ->setCountry("Germany");
$coordinates = $geocoding->getCoordinates();
$lat = $coordinates["latitude"];
$lng = $coordinates["longitude"];
```
The above example will return the following coordinates:
```json
{
    "latitude": 48.4253584,
    "longitude": 9.956179
}
``` 
</details>

<details>
<summary><b>Reverse-geocode coordinates to an address</b></summary>

The following example shows how to reverse-geocode coordinates to an address:
```php
<?php

use jensostertag\Geocoding\Geocoding;
    
$geocoding = new Geocoding();
$geocoding->setCoordinates(48.4253584, 9.956179)
          ->toAddress();
$address = $geocoding->getAddress();
$street = $address["street"];
$houseNumber = $address["houseNumber"];
$city = $address["city"];
$zipCode = $address["zipCode"];
$country = $address["country"];
$formattedAddress = $geocoding->getFormattedAddress();
```
The above example will return the following address:
```json
{
    "street": "James-Franck-Ring",
    "houseNumber": null,
    "city": "Ulm",
    "zipCode": "89081",
    "country": "Deutschland"
}
```
The formatted address will also be an array with two formatting options, inline and with `\n` line breaks:
```json
{
    "inline": "James-Franck-Ring, 89081 Ulm, Deutschland (DE)",
    "lineBreaks": "James-Franck-Ring\n89081 Ulm\nDeutschland (DE)"
}
```
</details>

<details>
<summary><b>Setting a custom user agent</b></summary>

You might want to set a custom user agent for your requests towards the Nominatim API to identify your application. To do that, use
```php
<?php

use jensostertag\Geocoding\Geocoding;

Geocoding::setUserAgent("MyApplication/1.0");
```
If you do not set a custom user agent, the default will be
```
Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0
```
</details>

<details>
<summary><b>Setting a custom Nominatim API URL</b></summary>

The public Nominatim API is very limited in the amount of requests you can send. If you want to use your own Nominatim API instance, you can set a custom URL for the API. To do that, use
```php
<?php

use jensostertag\Geocoding\Geocoding;

Geocoding::setApiUrl("https://nominatim.mydomain.com");
```
</details>
