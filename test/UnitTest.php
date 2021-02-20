<?php

namespace AddressStringParser\Test;

use PHPUnit\Framework\TestCase;

class UnitTest extends TestCase
{
    public function testConstructor()
    {
        $parser = new \AddressStringParser\Parser();
        $this->assertInstanceOf('AddressStringParser\Parser', $parser);
        $store  = new \AddressStringParser\ValueStore();
        $this->assertInstanceOf('AddressStringParser\ValueStore', $store);
    }

    public function testWellFormedString(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('900 North 3rd Street Ste 13A; Baton Rouge, Louisiana 70802-5236; United States');
        $expected = [
            'addressLine1'     => '900 North 3rd St',
            'addressLine2'     => 'Ste 13A',
            'zip'              => '70802',
            'zip4'             => '5236',
            'stateName'        => 'Louisiana',
            'state'            => 'LA',
            'city'             => 'Baton Rouge',
            'streetNumber'     => '900',
            'streetName'       => 'North 3rd',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => 'United States',
            'formattedAddress' => '900 North 3rd St, Ste 13A, Baton Rouge, LA 70802',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testNoDelimiterString(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('900 North 3rd Street Ste 13A Baton Rouge Louisiana 70802-5236');
        $expected = [
            'addressLine1'     => '900 North 3rd St',
            'addressLine2'     => 'Ste 13A',
            'zip'              => '70802',
            'zip4'             => '5236',
            'stateName'        => 'Louisiana',
            'state'            => 'LA',
            'city'             => 'Baton Rouge',
            'streetNumber'     => '900',
            'streetName'       => 'North 3rd',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '900 North 3rd St, Ste 13A, Baton Rouge, LA 70802',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testStreetOnly(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('900 North 3rd Street');
        $expected = [
            'addressLine1'     => '900 North 3rd St',
            'addressLine2'     => null,
            'zip'              => null,
            'zip4'             => null,
            'stateName'        => null,
            'state'            => null,
            'city'             => null,
            'streetNumber'     => '900',
            'streetName'       => 'North 3rd',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => null,
            'formattedAddress' => null,
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testStreetDirection(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('900 North 3rd Street SE, Baton Rouge, LA 70802');
        $expected = [
            'addressLine1'     => '900 North 3rd St SE',
            'addressLine2'     => null,
            'zip'              => '70802',
            'zip4'             => null,
            'stateName'        => 'Louisiana',
            'state'            => 'LA',
            'city'             => 'Baton Rouge',
            'streetNumber'     => '900',
            'streetName'       => 'North 3rd',
            'routeType'        => 'St',
            'streetDirection'  => 'SE',
            'country'          => 'USA',
            'formattedAddress' => '900 North 3rd St SE, Baton Rouge, LA 70802',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testPOBox(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('PO BOX 65502,TUCSON AZ 85728,USA');
        $expected = [
            'addressLine1'     => 'PO BOX 65502',
            'addressLine2'     => null,
            'zip'              => '85728',
            'zip4'             => null,
            'stateName'        => 'Arizona',
            'state'            => 'AZ',
            'city'             => 'TUCSON',
            'streetNumber'     => null,
            'streetName'       => null,
            'routeType'        => null,
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => 'PO BOX 65502, TUCSON, AZ 85728',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringAK(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('120 4th St, Juneau, AK 99801');
        $expected = [
            'addressLine1'     => '120 4th St',
            'addressLine2'     => null,
            'zip'              => '99801',
            'zip4'             => null,
            'stateName'        => 'Alaska',
            'state'            => 'AK',
            'city'             => 'Juneau',
            'streetNumber'     => '120',
            'streetName'       => '4th',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '120 4th St, Juneau, AK 99801',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringAL(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('600 Dexter Avenue, Montgomery, Alabama 36104');
        $expected = [
            'addressLine1'     => '600 Dexter Ave',
            'addressLine2'     => null,
            'zip'              => '36104',
            'zip4'             => null,
            'stateName'        => 'Alabama',
            'state'            => 'AL',
            'city'             => 'Montgomery',
            'streetNumber'     => '600',
            'streetName'       => 'Dexter',
            'routeType'        => 'Ave',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '600 Dexter Ave, Montgomery, AL 36104',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringAZ(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('1700 W Washington St, Phoenix, AZ 85007');
        $expected = [
            'addressLine1'     => '1700 W Washington St',
            'addressLine2'     => null,
            'zip'              => '85007',
            'zip4'             => null,
            'stateName'        => 'Arizona',
            'state'            => 'AZ',
            'city'             => 'Phoenix',
            'streetNumber'     => '1700',
            'streetName'       => 'W Washington',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '1700 W Washington St, Phoenix, AZ 85007',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringAR(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('500 Woodlane St., Little Rock, AR 72201');
        $expected = [
            'addressLine1'     => '500 Woodlane St',
            'addressLine2'     => null,
            'zip'              => '72201',
            'zip4'             => null,
            'stateName'        => 'Arkansas',
            'state'            => 'AR',
            'city'             => 'Little Rock',
            'streetNumber'     => '500',
            'streetName'       => 'Woodlane',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '500 Woodlane St, Little Rock, AR 72201',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringCA(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('1315 10th St room b-27, Sacramento, CA 95814');
        $expected = [
            'addressLine1'     => '1315 10th St',
            'addressLine2'     => 'Room B-27',
            'zip'              => '95814',
            'zip4'             => null,
            'stateName'        => 'California',
            'state'            => 'CA',
            'city'             => 'Sacramento',
            'streetNumber'     => '1315',
            'streetName'       => '10th',
            'routeType'        => 'St',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '1315 10th St, Room B-27, Sacramento, CA 95814',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringCO(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('200 E Colfax Ave., Denver, CO 80203');
        $expected = [
            'addressLine1'     => '200 E Colfax Ave',
            'addressLine2'     => null,
            'zip'              => '80203',
            'zip4'             => null,
            'stateName'        => 'Colorado',
            'state'            => 'CO',
            'city'             => 'Denver',
            'streetNumber'     => '200',
            'streetName'       => 'E Colfax',
            'routeType'        => 'Ave',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '200 E Colfax Ave, Denver, CO 80203',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringCT(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress("210 Capitol Ave.\nHartford, CT 06106");
        $expected = [
            'addressLine1'     => '210 Capitol Ave',
            'addressLine2'     => null,
            'zip'              => '06106',
            'zip4'             => null,
            'stateName'        => 'Connecticut',
            'state'            => 'CT',
            'city'             => 'Hartford',
            'streetNumber'     => '210',
            'streetName'       => 'Capitol',
            'routeType'        => 'Ave',
            'streetDirection'  => null,
            'country'          => 'USA',
            'formattedAddress' => '210 Capitol Ave, Hartford, CT 06106',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    public function testUSStringDC(): void
    {
        $parser = new \AddressStringParser\Parser();
        $parsedAddress = $parser->parseAddress('1600 Pennsylvania Ave. NW Ofc. 6-A Washington, DC 20500-0004');
        $expected = [
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
            'formattedAddress' => '1600 Pennsylvania Ave NW, Ofc. 6-A, Washington, DC 20500',
            'error'            => false,
            'errorMessage'     => null,
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $parsedAddress[$key]);
        }
    }

    // public function testOne(): void
    // {
    //     $parser = new \AddressStringParser\Parser();
    //     $parsedAddress = $parser->parseAddress("275 Old Highway 15 South");
    //     $this->assertNotTrue($parsedAddress['error']);
    // }

    // public function testUSAddressFile(): void
    // {
    //     $addresses = explode('|', file_get_contents(__DIR__ . '/addresses.log'));
    //     $parser    = new \AddressStringParser\Parser();
    //     foreach ($addresses as $address) {
    //         if (strlen($address)) {
    //             $parsedAddress = $parser->parseAddress($address);
    //             if ($parsedAddress['error']) {
    //                 echo $address;
    //             }
    //             $this->assertNotTrue($parsedAddress['error']);
    //         }
    //     }
    // }
}
