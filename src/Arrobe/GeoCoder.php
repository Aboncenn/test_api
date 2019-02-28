<?php

namespace App\Arrobe;

use GuzzleHttp\Client as GuzzleClient;

/**
 * GeoCoder traduit une adresse en Latitude/Longitude avec l'API OpenStreetMap : Nominatim
 * @see https://wiki.openstreetmap.org/wiki/Nominatim
 */
class GeoCoder{

    private $baseApiUrl = 'https://nominatim.openstreetmap.org/search.php?';
    private $requestDone = false;
    private $address;
    private $postalcode;
    private $city;
    private $query;
    private $latitude;
    private $longitude;

    public function setAddress(string $a): self
    {
        $this->address = $a;
        return $this;
    }

    public function setPostalCode(string $pc): self
    {
        $this->postalcode = $pc;
        return $this;
    }

    public function setCity(string $c): self
    {
        $this->city = $c;
        return $this;
    }

    public function setQuery(string $q): self
    {
        $this->query = $q;
        return $this;
    }

    private function getUrlArgs(): string
    {
        $a = '';
        if (!is_null($this->address) && !is_null($this->postalcode) && !is_null($this->city)){
            $a.='street='.$this->address;
            $a.='&postalcode='.$this->postalcode;
            $a.='&city='.$this->city;
        }elseif (!is_null($this->query)) {
            $a.='q='.$this->query;
        }else{
            // No arguments for request = no request to do
            $this->requestDone = true;
        }
        $a.='&format=json';
        $a.='&limit=1';
        return $a;
    }

    private function requestApi(): self
    {
        $args = $this->getUrlArgs();
        if (!$this->requestDone){
            $api = new GuzzleClient();
            $result = $api->request('GET', $this->baseApiUrl.$args);
            if (strpos($result->getHeader('Content-Type')[0], 'application/json') === false){
                throw new \Exception("Nominatim a repondu dans un format non pris en charge", 1);
            }
            $result = json_decode($result->getBody());
            if (sizeof($result) > 0){
                $result = $result[0];
                $this->latitude = $result->{'lat'};
                $this->longitude = $result->{'lon'};
            }
            $this->requestDone = true;
        }
        return $this;
    }

    public function getLat(): ?float
    {
        $this->requestApi();
        return $this->latitude;
    }

    public function getLng(): ?float
    {
        $this->requestApi();
        return $this->longitude;
    }

    public function getLatLng(): array
    {
        return [$this->getLat(), $this->getLng()];
    }

}
