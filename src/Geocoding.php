<?php

namespace struktal\Geocoding;
use struktal\Curl\Curl;

class Geocoding {
    private static string $API_URL = "https://nominatim.openstreetmap.org/";
    private static string $USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0";
    private array $address;
    private array $coordinates;

    /**
     * Set a custom Nominatim API URL, e.g. for a local Installation
     * @param string $apiUrl API URL
     * @return void
     */
    public static function setApiUrl(string $apiUrl): void {
        self::$API_URL = $apiUrl;
    }

    /**
     * Set a custom User Agent that identifies your Application
     * @param string $userAgent
     * @return void
     */
    public static function setUserAgent(string $userAgent): void {
        self::$USER_AGENT = $userAgent;
    }

    public function __construct() {
        $this->coordinates = array(
            "latitude" => null,
            "longitude" => null
        );
        $this->address = array(
            "street" => null,
            "houseNumber" => null,
            "city" => null,
            "zipCode" => null,
            "country" => null
        );
    }

    /**
     * Set the Coordinates that should be used for reverse-geocoding
     * @param float $latitude Latitude (>= -90 && <= 90)
     * @param float $longitude Longitude (>= -180 && <= 180)
     * @return $this
     */
    public function setCoordinates(float $latitude, float $longitude): Geocoding {
        if(($latitude >= -90 || $latitude <= 90) && ($longitude >= -180 || $longitude <= 180)) {
            $this->coordinates["latitude"] = $latitude;
            $this->coordinates["longitude"] = $longitude;
        } else {
            $this->coordinates["latitude"] = null;
            $this->coordinates["longitude"] = null;
        }

        return $this;
    }

    /**
     * Set the Street that should be used for geocoding
     * @param string $street Street
     * @return $this
     */
    public function setStreet(string $street): Geocoding {
        $this->address["street"] = $street;
        return $this;
    }

    /**
     * Set the House Number that should be used for geocoding
     * @param string $houseNumber House Number
     * @return $this
     */
    public function setHouseNumber(string $houseNumber): Geocoding {
        $this->address["houseNumber"] = $houseNumber;
        return $this;
    }

    /**
     * Set the City that should be used for geocoding
     * @param string $city City
     * @return $this
     */
    public function setCity(string $city): Geocoding {
        $this->address["city"] = $city;
        return $this;
    }

    /**
     * Set the ZIP Code that should be used for geocoding
     * @param string $zipCode ZIP Code
     * @return $this
     */
    public function setZipCode(string $zipCode): Geocoding {
        $this->address["zipCode"] = $zipCode;
        return $this;
    }

    /**
     * Set the Country that should be used for geocoding
     * @param string $country Country
     * @return $this
     */
    public function setCountry(string $country): Geocoding {
        $this->address["country"] = $country;
        return $this;
    }

    /**
     * Set the Country Code that should be used for geocoding
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode(string $countryCode): Geocoding {
        $this->address["countryCode"] = $countryCode;
        return $this;
    }

    /**
     * Get the geocoded Coordinates
     * @return null[]|float[]
     */
    public function getCoordinates(): array {
        return $this->coordinates;
    }

    /**
     * Get the reverse-geocoded Address
     * @return null[]|string[]
     */
    public function getAddress(): array {
        return $this->address;
    }

    /**
     * Get formatted Strings for the reverse-geocoded Address
     * @return string[]
     */
    public function getFormattedAddress(): array {
        $addressInline = "";
        $addressLineBreaks = "";

        if(isset($this->address["street"])) {
            $addressInline .= $this->address["street"];
            $addressLineBreaks .= $this->address["street"];

            if(isset($this->address["houseNumber"])) {
                $addressInline .= " " . $this->address["houseNumber"];
                $addressLineBreaks .= " " . $this->address["houseNumber"];
            }
        }

        if(isset($this->address["zipCode"]) || isset($this->address["city"])) {
            $addressInline .= ", ";
            $addressLineBreaks .= PHP_EOL;
        }

        if(isset($this->address["zipCode"])) {
            $addressInline .= $this->address["zipCode"];
            $addressLineBreaks .= $this->address["zipCode"];

            if(isset($this->address["city"])) {
                $addressInline .= " ";
                $addressLineBreaks .= " ";
            }
        }

        if(isset($this->address["city"])) {
            $addressInline .= $this->address["city"];
            $addressLineBreaks .= $this->address["city"];
        }

        if(isset($this->address["country"]) || isset($this->address["countryCode"])) {
            $addressInline .= ", ";
            $addressLineBreaks .= PHP_EOL;
        }

        if(isset($this->address["countryCode"])) {
            if(isset($this->address["country"])) {
                $addressInline .= $this->address["country"] . " (" . $this->address["countryCode"] . ")";
                $addressLineBreaks .= $this->address["country"] . " (" . $this->address["countryCode"] . ")";
            } else {
                $addressInline .= $this->address["countryCode"];
                $addressLineBreaks .= $this->address["countryCode"];
            }
        } else {
            if(isset($this->address["country"])) {
                $addressInline .= $this->address["country"];
                $addressLineBreaks .= $this->address["country"];
            }
        }

        return array(
            "inline" => $addressInline,
            "lineBreaks" => $addressLineBreaks
        );
    }

    /**
     * Reverse-geocode the given Coordinates
     * @return $this
     */
    public function toAddress(): Geocoding {
        $url = self::$API_URL . "reverse?format=json";
        $url .= "&lat=" . $this->coordinates["latitude"];
        $url .= "&lon=" . $this->coordinates["longitude"];
        $url = str_replace(" ", "%20", $url);

        $curl = new Curl();
        $curl->setUrl($url);
        $curl->setMethod(Curl::$METHOD_GET);
        $curl->addHeader("Content-Type: application/json");
        $curl->addHeader("User-Agent: " . self::$USER_AGENT);
        $response = json_decode($curl->execute(), true);

        foreach(array("road", "street") as $key) {
            if(isset($response["address"][$key])) {
                $this->setStreet($response["address"][$key]);
                break;
            }
        }

        foreach(array("house_number", "housenumber") as $key) {
            if(isset($response["address"][$key])) {
                $this->setHouseNumber($response["address"][$key]);
                break;
            }
        }

        foreach(array("city", "town", "village", "quarter") as $key) {
            if(isset($response["address"][$key])) {
                $this->setCity($response["address"][$key]);
                break;
            }
        }

        foreach(array("postcode", "postalcode") as $key) {
            if(isset($response["address"][$key])) {
                $this->setZipCode($response["address"][$key]);
                break;
            }
        }

        foreach(array("country") as $key) {
            if(isset($response["address"][$key])) {
                $this->setCountry($response["address"][$key]);
                break;
            }
        }

        foreach(array("country_code", "countrycode") as $key) {
            if(isset($response["address"][$key])) {
                $this->setCountryCode(strtoupper($response["address"][$key]));
                break;
            }
        }

        return $this;
    }

    /**
     * Geocode the given Address
     * @return $this
     */
    public function toCoordinates(): Geocoding {
        $url = self::$API_URL . "search?format=json";
        $url .= "&street=" . $this->address["street"] . " " . $this->address["houseNumber"];
        $url .= "&city=" . $this->address["city"];
        $url .= "&postalcode=" . $this->address["zipCode"];
        $url .= "&country=" . $this->address["country"];
        $url = str_replace(" ", "%20", $url);

        $curl = new Curl();
        $curl->setUrl($url);
        $curl->setMethod(Curl::$METHOD_GET);
        $curl->addHeader("Content-Type: application/json");
        $curl->addHeader("User-Agent: " . self::$USER_AGENT);
        $response = json_decode($curl->execute(), true);

        $this->setCoordinates($response[0]["lat"], $response[0]["lon"]);

        return $this;
    }
}
