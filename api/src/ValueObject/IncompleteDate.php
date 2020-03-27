<?php

namespace App\ValueObject;

/*
 * Incomplomplete data class
 *
 * This doctrine value object is designd to work in tendem with the incompleteData mapping type to provide doctrine support for the working with incomplete date objects
 *
 *
 */

class IncompleteDate
{
    /**
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function __construct($year, $month, $day)
    {
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        // If the day is missing we return zero
        if (!$this->day) {
            return 0;
        }

        return $this->day;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        // If the month is missing we return zero
        if (!$this->month) {
            return 0;
        }

        return $this->month;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        // If the year is missing we return zero
        if (!$this->year) {
            return 0;
        }

        return $this->year;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return sprintf('%04u-%02u-%02u', $this->getYear(), $this->getMonth(), $this->getDay());
    }
}
