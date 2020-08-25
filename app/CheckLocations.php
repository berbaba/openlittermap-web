<?php

namespace App;

use Log;
use App\Events\NewCityAdded;
use App\Events\NewStateAdded;
use App\Events\NewCountryAdded;
use Illuminate\Support\Facades\Redis;

trait CheckLocations
{
	protected $country;
    protected $countryCode;
    protected $district;
    protected $state;
    protected $city;
    protected $suburb;

    protected function checkCountry ($addressArray)
    {
        if ($this->country) return $this->country;

        if (array_key_exists('country', $addressArray))
        {
            $this->country = $addressArray['country'];
        } else {
            $this->country = 'error_country'; // error country
        }

        if (array_key_exists('country_code', $addressArray))
        {
            $this->countryCode = $addressArray["country_code"];
        } else {
            $this->countryCode = 'error';
        }

        if ($this->country != 'error_country' && $this->countryCode != 'error')
        {
            \Log::info(['check_country', $this->country]);
            if (! Redis::sismember('countries', $this->country))
            {
                \Log::info('not_found');
                Redis::sadd('countries', $this->country);
                event(new NewCountryAdded($this->country, $this->countryCode, now()));
            }
        }
    }

    protected function checkState ($addressArray)
    {
        if ($this->state) return $this->state;

        if (array_key_exists('state', $addressArray))
        {
            $this->state = $addressArray["state"];
        }

        if (! $this->state)
        {
            if (array_key_exists('county', $addressArray))
            {
                $this->state = $addressArray["county"];
            }
        }

        if (! $this->state)
        {
            if (array_key_exists('region', $addressArray))
            {
                $this->state = $addressArray["region"];
            }
        }

        if (! $this->state)
        {
            $this->state = 'error_state';
        }

        if ($this->state != 'error_state')
        {
            if(! Redis::sismember('states', $this->state))
            {
                Redis::sadd('states', $this->state);
                $this->checkCountry($addressArray);
                event(new NewStateAdded($this->state, $this->country, now()));
            }
        }
    }

    // check for state_district, postcode, zip
    protected function checkDistrict ($addressArray)
    {
        if ($this->district) return $this->district;

        if (array_key_exists('postcode', $addressArray))
        {
            $this->district = $addressArray['postcode'];
        }

        if (! $this->district)
        {
            if (array_key_exists('zip', $addressArray))
            {
                $this->district = $addressArray['zip'];
            }
        }

        if (! $this->district)
        {
            if (array_key_exists('state_district', $addressArray))
            {
                $this->district = $addressArray['state_district'];
            }
        }

        if (! $this->district)
        {
            $this->district = 'unknown';
        }
    }

    protected function checkCity ($addressArray)
    {
        if ($this->city) return $this->city;

        // city, town, hamlet, city_district, village
        if (array_key_exists('city', $addressArray))
        {
            $this->city = $addressArray['city'];
        }

        if (! $this->city)
        {
            if (array_key_exists('town', $addressArray))
            {
                $this->city = $addressArray['town'];
            }
        }

        if (! $this->city)
        {
            if (array_key_exists('city_district', $addressArray))
            {
                $this->city = $addressArray['city_district'];
            }
        }

        if (! $this->city)
        {
            if (array_key_exists('village', $addressArray))
            {
                $this->city = $addressArray['village'];
            }
        }

        if (! $this->city)
        {
            if (array_key_exists('hamlet', $addressArray))
            {
                $this->city = $addressArray['hamlet'];
            }
        }

        if (! $this->city)
        {
            if (array_key_exists('locality', $addressArray))
            {
                $this->city = $addressArray['locality'];
            }
        }

        if (! $this->city)
        {
            if (array_key_exists('county', $addressArray))
            {
                $this->city = $addressArray['county'];
            }
        }

        if (! $this->city)
        {
            $this->city = 'error_city';
        }

        if ($this->city != 'error_city')
        {
            if (! Redis::sismember('cities', $this->city))
            {
                Redis::sadd('cities', $this->city);
                $this->checkCountry($addressArray);
                $this->checkState($addressArray);
                event(new NewCityAdded($this->city, $this->state, $this->country, now()));
            }
        }
    }

    protected function checkSuburb ($addressArray)
    {
        // check for suburb, locality, quarter, borough, neighbourhood, city_block
        if ($this->suburb) return $this->suburb;

        if (array_key_exists('suburb', $addressArray))
        {
            $this->suburb = $addressArray['suburb'];
        }

        if (! $this->suburb)
        {
            if (array_key_exists('locality', $addressArray))
            {
                $this->suburb = $addressArray['locality'];
            }
        }

        if (! $this->suburb)
        {
            if (array_key_exists('quarter', $addressArray))
            {
                $this->suburb = $addressArray['quarter'];
            }
        }

        if (! $this->suburb)
        {
            if (array_key_exists('borough', $addressArray))
            {
                $this->suburb = $addressArray['borough'];
            }
        }

        if (! $this->suburb)
        {
            if (array_key_exists('neighbourhood', $addressArray))
            {
                $this->suburb = $addressArray['neighbourhood'];
            }
        }

        if (! $this->suburb)
        {
            if (array_key_exists('city_block', $addressArray))
            {
                $this->suburb = $addressArray['city_block'];
            }
        }

        if (! $this->suburb)
        {
            $this->suburb = 'unknown';
        }
    }
}