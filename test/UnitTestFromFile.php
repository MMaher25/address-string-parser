<?php

namespace AddressStringParser\Test;

use PHPUnit\Framework\TestCase;

class UnitTestFromFile extends TestCase
{
    public function testConstructor()
    {
        $parser = new \AddressStringParser\Parser();
        $this->assertInstanceOf('AddressStringParser\Parser', $parser);
        $store  = new \AddressStringParser\ValueStore();
        $this->assertInstanceOf('AddressStringParser\ValueStore', $store);
    }

    public function testUSAddressFile(): void
    {
        $addresses = explode('|', file_get_contents(__DIR__ . '/addresses.log'));
        $parser    = new \AddressStringParser\Parser();
        foreach ($addresses as $address) {
            if (strlen($address)) {
                $parsedAddress = $parser->parseAddress($address);
                if ($parsedAddress['zip'] !== null) {
                    $this->assertIsNumeric($parsedAddress['zip']);
                }
                if ($parsedAddress['zip4'] !== null) {
                    $this->assertIsNumeric($parsedAddress['zip4']);
                }
                if ($parsedAddress['streetNumber'] !== null) {
                    $this->assertIsNumeric($parsedAddress['streetNumber']);
                }
                $this->assertNotTrue($parsedAddress['error']);
            }
        }
    }
}
