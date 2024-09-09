<?php

/**
 * Function to generate paydays and timesheet deadlines based on rules, avoiding weekends and public holidays.
 *
 * @param int $year The year for which the iCalendar file should be generated.
 * @return string The iCalendar content.
 */
function generateICalendar(int $year): string
{
    // Set the timezone to New Zealand
    date_default_timezone_set('Pacific/Auckland');

    // Start the iCalendar content
    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//Your Company//NONSGML v1.0//EN\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";

    // Get the list of public holidays for the given year
    $publicHolidays = getNewZealandPublicHolidays($year);

    // Loop through each month of the year
    for ($month = 1; $month <= 12; $month++) {
        // Calculate the payday dates
        $paydays = [
            getAdjustedDate($year, $month, 15, $publicHolidays),
            getAdjustedDate($year, $month, ($month == 2 ? (isLeapYear($year) ? 29 : 28) : 30), $publicHolidays)
        ];

        foreach ($paydays as $payday) {
            // Add payday to iCalendar
            $ical .= createEvent($payday, "Payday");

            // Calculate timesheet deadline two business days before payday
            $timesheetDeadline = getBusinessDaysBefore($payday, 2, $publicHolidays);
            $ical .= createEvent($timesheetDeadline, "Timesheet Deadline");
        }
    }

    // End the iCalendar content
    $ical .= "END:VCALENDAR\r\n";

    return $ical;
}

/**
 * Function to create an iCalendar event.
 *
 * @param DateTime $date The date of the event.
 * @param string $summary The summary/description of the event.
 * @return string The iCalendar event string.
 */
function createEvent(DateTime $date, string $summary): string
{
    return "BEGIN:VEVENT\r\n" .
        "UID:" . uniqid() . "@example.com\r\n" .
        "DTSTAMP:" . $date->format('Ymd\THis\Z') . "\r\n" .
        "DTSTART;VALUE=DATE:" . $date->format('Ymd') . "\r\n" .
        "SUMMARY:" . $summary . "\r\n" .
        "END:VEVENT\r\n";
}

/**
 * Function to adjust the given date to the previous business day if it falls on a weekend or a public holiday.
 *
 * @param int $year The year of the date.
 * @param int $month The month of the date.
 * @param int $day The day of the date.
 * @param array $publicHolidays Array of public holiday dates to avoid.
 * @return DateTime The adjusted date.
 */
function getAdjustedDate(int $year, int $month, int $day, array $publicHolidays): DateTime
{
    $date = new DateTime("$year-$month-$day");

    // Adjust to the previous business day if the date falls on a weekend or public holiday
    while ($date->format('N') >= 6 || in_array($date->format('Y-m-d'), $publicHolidays)) { // Weekend or public holiday
        $date->modify('last Friday');
    }

    return $date;
}

/**
 * Function to calculate a date that is a specific number of business days before a given date,
 * avoiding weekends and public holidays.
 *
 * @param DateTime $date The original date.
 * @param int $businessDays The number of business days before the given date.
 * @param array $publicHolidays Array of public holiday dates to avoid.
 * @return DateTime The adjusted date.
 */
function getBusinessDaysBefore(DateTime $date, int $businessDays, array $publicHolidays): DateTime
{
    $daysBefore = clone $date;

    // Calculate the required number of business days
    while ($businessDays > 0) {
        $daysBefore->modify('-1 day');

        // Skip weekends and public holidays
        if ($daysBefore->format('N') < 6 && !in_array($daysBefore->format('Y-m-d'), $publicHolidays)) {
            $businessDays--;
        }
    }

    // Adjust to the previous Friday if it falls on a weekend or public holiday
    while ($daysBefore->format('N') >= 6 || in_array($daysBefore->format('Y-m-d'), $publicHolidays)) { // Weekend or public holiday
        $daysBefore->modify('last Friday');
    }

    return $daysBefore;
}

/**
 * Function to check if a year is a leap year.
 *
 * @param int $year The year to check.
 * @return bool True if it's a leap year, false otherwise.
 */
function isLeapYear(int $year): bool
{
    return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
}

/**
 * Function to get an array of New Zealand public holidays for the given year.
 * This list should be updated annually or managed dynamically via an API if available.
 *
 * @param int $year The year for which to fetch public holidays.
 * @return array An array of public holiday dates in 'Y-m-d' format.
 */
function getNewZealandPublicHolidays(int $year): array
{
    return [
        "$year-01-01", // New Year's Day
        "$year-01-02", // Day after New Year's Day
        date('Y-m-d', strtotime("4th Monday of January $year")), // Wellington Anniversary Day (example of variable holiday)
        "$year-02-06", // Waitangi Day
        date('Y-m-d', strtotime("Friday before Easter $year")), // Good Friday
        date('Y-m-d', strtotime("Easter Monday $year")), // Easter Monday
        "$year-04-25", // ANZAC Day
        date('Y-m-d', strtotime("1st Monday of June $year")), // King's Birthday
        date('Y-m-d', strtotime("4th Monday of October $year")), // Labour Day
        "$year-12-25", // Christmas Day
        "$year-12-26", // Boxing Day
        // Add more holidays or adjust as per the official calendar
    ];
}

// Set the year for which the calendar is to be generated (changeable by the user)
$year = 2024;

// Generate the iCalendar content
$icalContent = generateICalendar($year);

// Define the filename for the .ical file
$filename = "paydays_$year.ics";

// Output the iCalendar file as a download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $icalContent;
