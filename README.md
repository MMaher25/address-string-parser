# address-string-parser

A PHP library for parsing US address strings into their component parts.

## Installation

This library can be installed via packagist, using composer:

    composer require mmaher/address-string-parser

## Usage

The Address Parser can be invoked on any reasonably-well-formed street address string to return an associative array of normalized base component parts.
```php
<?php
$parser = new AddressStringParser\Parser();

$addressString = '1600 Pennsylvania Ave. NW Ofc. 6-A Washington, DC 20500-0004';
$addressArray  = $parser->parseAddress($addressString);
```
The output of which would be:
```php
$addressArray = [
        'addressLine1'     => '1600 Pennsylvania Ave NW',
        'addressLine2'     => 'Ofc. 6-A',
        'zip'              => '20500',
        'zip4'             => '0004',
        'stateName'        => 'District Of Columbia',
        'state'            => 'DC',
        'city'             => 'Washington',
        'streetNumber'     => '1600',
        'streetName'       => 'Pennsylvania',
        'routeType'        => 'Ave',
        'streetDirection'  => 'NW',
        'country'          => 'USA',
        'formattedAddress' => '1600 Pennsylvania Ave NW, Ofc. 6-A, Washington, DC 20500-0004',
        'error'            => false,
        'errorMessage'     => null,
];
```
