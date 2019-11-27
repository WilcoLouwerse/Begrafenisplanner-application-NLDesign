<?php

namespace App\ValueObject;

class UnderInvestigation
{
    /**
     * @param array  $properties
     * @param string $date
     */
    public function __construct($properties, $date)
    {
        $this->properties = $properties;
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }
}
