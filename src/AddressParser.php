<?php

/**
 * address-string-parser
 *
 * @link       https://github.com/MMaher23/address-string-parser
 * @author     Mark Maher
 * @copyright  Copyright (c) 2021
 */

/**
 * @namespace
 */

namespace AddressStringParser;

/**
 * Address parser class
 *
 * @author     Mark Maher
 * @copyright  Copyright (c) 2021
 * @version    1.0.0
 */
class Parser
{
    /**
     * Parse address string into component parts
     *
     * @param  string $address
     * @return array
     */
    public function parseAddress($address)
    {
        if (empty($address)) {
            $parsed['error']        = true;
            $parsed['errorMessage'] = 'Must supply non-empty string';
            return $parsed;
        }
        $parsed = [
            'addressLine1'    => null,
            'addressLine2'    => null,
            'zip'             => null,
            'zip4'            => null,
            'stateName'       => null,
            'state'           => null,
            'city'            => null,
            'streetName'      => null,
            'streetNumber'    => null,
            'streetDirection' => null,
            'routeType'       => null,
            'country'         => null,
            'error'           => false,
            'errorMessage'    => null,
        ];
        $store = new ValueStore();

        // Handle double spaces
        $address = str_replace('  ', ' ', $address);
        // Handle bad formatting
        $address = str_replace([' -', '- '], '-', $address);

        // Assume comma, newline and tab is an intentional delimiter
        $addressArray = preg_split('/,|\t|\n/', $address);

        $streetSection             = "";
        $usStreetDirectionalString = implode('|', array_keys($store->getDirections));
        $usLine2String             = implode('|', array_keys($store->getPrefixes));
        $streetRegex               = '/.*\b(?:' . implode('|', $store->getRouteTypes) . ')\b\.?' . '( +(?:' . $usStreetDirectionalString . ')\b)?/i';
        if (count($addressArray) === 1) {
            // No commas, let's see if it's just a street address
            $streetOnlyRegex = str_replace('?/i', '?$/i', $streetRegex);
            if (preg_match($streetOnlyRegex, $address, $streetMatches) === 1) {
                $parsed['addressLine1'] = $streetMatches[0];
                $streetSection          = preg_replace($streetRegex, '', $streetSection);
                if ($streetSection && strlen($streetSection)) {
                    // Check if line2 data was already parsed
                    if ($parsed['addressLine2'] !== null) {
                        $parsed['error']        = true;
                        $parsed['errorMessage'] = 'Can not parse address. Too many address lines. ';
                        return $parsed;
                    } else {
                        $parsed['addressLine2'] = $streetSection;
                    }
                }
                $streetParts = explode(' ', $parsed['addressLine1']);

                // Check if directional is last element
                $dirRegex = '/\.\*\b(?:' . $usStreetDirectionalString . ')$/';
                if (preg_match($dirRegex, $parsed['addressLine1']) === 1) {
                    $parsed['streetDirection'] = strtoupper(array_pop($streetParts));
                }

                // Assume type is last and number is first   
                $parsed['streetNumber'] = $streetParts[0]; // Assume number is first element

                // If there are only 2 street parts (number and name) then its likely missing a "real" suffix and the street name just happened to match a suffix
                if (count($streetParts) > 2) {
                    // Remove '.' if it follows routeType
                    $streetParts[count($streetParts) - 1] = preg_replace('/\.$/', '', $streetParts[count($streetParts) - 1]);
                    $parsed['routeType'] = ucwords($store->getRouteTypes[strtolower($streetParts[count($streetParts) - 1])]);
                }

                $parsed['streetName'] = $streetParts[1]; // Assume street name is everything in the middle
                for ($i = 2; $i < count($streetParts) - 1; $i++) {
                    $parsed['streetName'] = $parsed['streetName'] + " " + $streetParts[$i];
                }
                $parsed['streetName']   = ucwords($parsed['streetName']);
                $parsed['addressLine1'] = implode(' ', [$parsed['streetNumber'], $parsed['streetName']]);

                if ($parsed['routeType'] !== null) {
                    $parsed['addressLine1'] = $parsed['addressLine1'] . ' ' . $parsed['routeType'];
                }
                if ($parsed['streetDirection']) {
                    $parsed['addressLine1'] = $parsed['addressLine1'] . ' ' . $parsed['streetDirection'];
                }
                return $parsed;
            }
        }

        // Check if the last section contains country reference (Just supports US for now)
        $countrySection = trim($addressArray[count($addressArray) - 1]);
        if ($countrySection === 'US' || $countrySection === 'USA' || $countrySection === 'United States' || $countrySection === 'Canada') {
            $parsed['country'] = $countrySection;
            array_splice($addressArray, -1);
        }

        // Assume the last address section contains state, zip or both
        $stateSection = trim($addressArray[count($addressArray) - 1]);
        if (preg_match('/(\d{5})-(\d{4})/', $stateSection, $zipMatches) === 1) {
            $parsed['country'] = $parsed['country'] ?? 'USA';
            $parsed['zip']     = $zipMatches[1];
            $parsed['zip4']    = $zipMatches[2];
        } else if (preg_match('/\d{9}/', $stateSection, $zipMatches) === 1) {
            $parsed['country'] = $parsed['country'] ?? 'USA';
            $parsed['zip']     = substr($zipMatches[0], 0, 5);
            $parsed['zip4']    = substr($zipMatches[0], 5);
        } else if (preg_match('/\d{5}/', $stateSection, $zipMatches) === 1) {
            $parsed['country'] = $parsed['country'] ?? 'USA';
            $parsed['zip']     = $zipMatches[0];
        } else if (preg_match('/[A-Z]\d[A-Z] ?\d[A-Z]\d/', $stateSection, $zipMatches) === 1) {
            // Canadian postal codes
            $parsed['country'] = $parsed['country'] ?? 'Canada';
            $parsed['zip']     = $zipMatches[0];
        }

        if ($parsed['zip'] !== null && $parsed['zip4'] !== null) {
            $stateSection = trim(str_replace($parsed['zip'] . '-' . $parsed['zip4'], '', $stateSection));
            $stateSection = trim(str_replace($parsed['zip'] . $parsed['zip4'], '', $stateSection));
        } else if ($parsed['zip'] !== null) {
            $stateSection = trim(str_replace($parsed['zip'], '', $stateSection));
        }

        // Parse and remove state
        if (strlen($stateSection) > 0) {
            $addressArray[count($addressArray) - 1] = $stateSection;
        } else {
            array_splice($addressArray, -1);
            $stateSection = trim($addressArray[count($addressArray) - 1]);
        }

        // Check for just a state code
        if (strlen($stateSection) == 2 && array_key_exists(strtoupper($stateSection), $store->getStates)) {
            $parsed['state']         = strtoupper($stateSection);
            $parsed['stateName']     = ucwords($store->getStates[strtoupper($stateSection)]);
            $stateSection            = trim(str_replace($parsed['state'], '', $stateSection));
        } else {
            // Next check if the state string ends in state name or code
            foreach ($store->getStates as $code => $state) {
                $regex = "/ " . $code . "$|" . $state . "$/i";
                if (preg_match($regex, $stateSection) === 1) {
                    $stateSection        = preg_replace($regex, '', $stateSection);
                    $parsed['state']     = $code;
                    $parsed['stateName'] = $state;
                    break;
                }
            }
        }
        if (empty($parsed['state']) || strlen($parsed['state']) != 2) {
            $parsed['error']        = true;
            $parsed['errorMessage'] = 'Could not determine state';
            return $parsed;
        }

        // Parse and remove city name
        $citySection = "";
        if (strlen($stateSection) > 0) {
            $addressArray[count($addressArray) - 1] = $stateSection;
            $citySection = $addressArray[count($addressArray) - 1];
        } else {
            array_splice($addressArray, -1);
            $citySection = trim($addressArray[count($addressArray) - 1]);
        }
        foreach ($store->getCities($parsed['state']) as $city) {
            $regex = '/' . $city . '$/';
            if (preg_match($regex, $citySection) === 1) {
                $citySection    = preg_replace($regex, '', $citySection);
                $parsed['city'] = $city;
                break;
            }
        }

        if ($parsed['city'] === null) {
            $parsed['city'] = ucwords($citySection);
            $citySection = "";
        }

        // Parse the street data

        if (strlen($citySection) > 0) {
            $addressArray[count($addressArray) - 1] = $citySection;
        } else {
            array_splice($addressArray, -1);
        }

        if (count($addressArray) > 2) {
            $parsed['error']        = true;
            $parsed['errorMessage'] = 'Can not parse address. Too many address lines.';
            return $parsed;
        } else if (count($addressArray) === 2) {
            // check if the secondary data is first
            $regex = '^(' . $usLine2String . ')\b';
            if (preg_match($regex, $addressArray[0]) === 1) {
                $tmpString       = $addressArray[1];
                $addressArray[1] = $addressArray[0];
                $addressArray[0] = $tmpString;
            }
            //Assume street line is first
            $parsed['addressLine2'] = trim($addressArray[1]);
            array_splice($addressArray, -1);
        }
        if (count($addressArray) === 1) {
            $streetSection = trim($addressArray[0]);
            // If no address line 2 exists check to see if it is incorrectly placed at the front of line 1
            if ($parsed['addressLine2'] === null) {
                $regex = '/^(' . $usLine2String . ')\s\S+/i';
                if (preg_match($regex, $streetSection, $streetMatches)) {
                    $parsed['addressLine2'] = $streetMatches[0];
                    $streetSection          = trim(preg_replace($regex, '', $streetSection));
                }
            }
            //Assume street address comes first and the rest is secondary address
            $poBoxRegex     = '/(P\\.?O\\.?|POST\\s+OFFICE)\\s+(BOX|DRAWER)\\s\\w+/i';
            $aveLetterRegex = '/\.\*\b(ave.?|avenue)\.\*\b[a-zA-Z]\b/i';
            $noSuffixRegex  = '/\b\d+\s[a-zA-Z0-9_ ]+\b/';
            if (preg_match($aveLetterRegex, $streetSection, $aveMatches) === 1) {
                $parsed['addressLine1'] = $aveMatches[0];
                $streetSection = trim(preg_replace($aveLetterRegex, '', $streetSection));
                if ($streetSection && strlen($streetSection) > 0) {
                    // Check if line2 data was already parsed
                    if ($parsed['addressLine2'] !== null) {
                        $parsed['error']        = true;
                        $parsed['errorMessage'] = 'Can not parse address. Too many address lines. ';
                        return $parsed;
                    } else {
                        $parsed['addressLine2'] = $streetSection;
                    }
                }

                $streetParts = explode(' ', $parsed['addressLine1']);
                // Assume type is last and number is first   
                $parsed['streetNumber'] = $$streetParts[0]; // Assume number is first element

                // Normalize to Ave
                $streetParts[count($streetParts) - 2] = preg_replace('/^(ave.?|avenue)$/i', 'Ave', $streetParts[count($streetParts) - 2]);

                $parsed['streetName'] = $streetParts[1]; // Assume street name is everything in the middle
                for ($i = 2; $i < count($streetParts); $i++) {
                    $parsed['streetName'] = $parsed['streetName'] + " " + $streetParts[$i];
                }
                $parsed['streetName']   = ucwords($parsed['streetName']);
                $parsed['addressLine1'] = implode(' ', [$parsed['streetNumber'], $parsed['streetName']]);
            } else if (preg_match($streetRegex, $streetSection, $streetMatches) === 1) {
                $parsed['addressLine1'] = $streetMatches[0];
                $streetSection          = preg_replace($streetRegex, '', $streetSection);
                if ($streetSection && strlen($streetSection)) {
                    // Check if line2 data was already parsed
                    if ($parsed['addressLine2'] !== null) {
                        $parsed['error']        = true;
                        $parsed['errorMessage'] = 'Can not parse address. Too many address lines. ';
                        return $parsed;
                    } else {
                        $parsed['addressLine2'] = $streetSection;
                    }
                }
                $streetParts = explode(' ', $parsed['addressLine1']);

                // Check if directional is last element
                $dirRegex = '/\.\*\b(?:' . $usStreetDirectionalString . ')$/';
                if (preg_match($dirRegex, $parsed['addressLine1']) === 1) {
                    $parsed['streetDirection'] = strtoupper(array_pop($streetParts));
                }

                // Assume type is last and number is first   
                $parsed['streetNumber'] = $streetParts[0]; // Assume number is first element

                // If there are only 2 street parts (number and name) then its likely missing a "real" suffix and the street name just happened to match a suffix
                if (count($streetParts) > 2) {
                    // Remove '.' if it follows routeType
                    $streetParts[count($streetParts) - 1] = preg_replace('/\.$/', '', $streetParts[count($streetParts) - 1]);
                    $parsed['routeType'] = ucwords($store->getRouteTypes[strtolower($streetParts[count($streetParts) - 1])]);
                }

                $parsed['streetName'] = $streetParts[1]; // Assume street name is everything in the middle
                for ($i = 2; $i < count($streetParts) - 1; $i++) {
                    $parsed['streetName'] = $parsed['streetName'] . " " . $streetParts[$i];
                }
                $parsed['streetName']   = ucwords($parsed['streetName']);
                $parsed['addressLine1'] = implode(' ', [$parsed['streetNumber'], $parsed['streetName']]);

                if ($parsed['routeType'] !== null) {
                    $parsed['addressLine1'] = $parsed['addressLine1'] . ' ' . $parsed['routeType'];
                }
                if ($parsed['streetDirection']) {
                    $parsed['addressLine1'] = $parsed['addressLine1'] . ' ' . $parsed['streetDirection'];
                }
            } else if (preg_match($poBoxRegex, $streetSection, $poBoxMatches)) {
                $parsed['addressLine1'] = $poBoxMatches[0];
                $streetSection = trim(preg_replace($poBoxRegex, '', $streetSection));
            } else if (preg_match($noSuffixRegex, $streetSection, $noSuffixMatches) === 1) {
                // Check for a line2 prefix followed by a single word. If found peel that off as addressLine2
                $line2Regex = '/\s(' . $usLine2String . ')\.?\s[a-zA-Z0-9_\-]+$/i';
                if (preg_match($line2Regex, $streetSection, $line2Matches)) {
                    $parsed['addressLine2'] = trim($line2Matches);
                    $streetSection          = preg_replace($line2Regex, '', $streetSection);
                }

                $parsed['addressLine1'] = $noSuffixMatches[0];
                $streetSection = trim(preg_replace($noSuffixRegex, '', $streetSection));
                $streetParts   = explode(' ', $parsed['addressLine1']);

                // Assume type is last and number is first   
                $parsed['streetNumber'] = array_shift($streetParts); // Assume number is first element
                $parsed['streetName']   = implode(' ', $streetParts); // Assume street name is everything else
            } else {
                $parsed['error']        = true;
                $parsed['errorMessage'] = 'Can not parse address. Invalid address.';
                return $parsed;
            }
        } else {
            $parsed['error']        = true;
            $parsed['errorMessage'] = 'Can not parse address. Invalid address.';
            return $parsed;
        }

        $addressString = $parsed['addressLine1'];
        if ($parsed['addressLine2'] !== null) {
            $addressString .= ', ' + $parsed['addressLine2'];
        }
        if ($addressString && $parsed['city'] !== null && $parsed['state'] !== null && $parsed['zip'] !== null) {
            $idString = $addressString . ", " . $parsed['city'] . ", " . $parsed['state'] . " " . $parsed['zip'];
            $parsed['formattedAddress'] = $idString;
        }

        return $parsed;
    }
}
