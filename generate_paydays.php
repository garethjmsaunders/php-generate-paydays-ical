<?php

// Set the year for which the calendar is to be generated (changeable by the user)
$year = 2024;

/**
 * Function to generate paydays and timesheet deadlines based on rules.
 *
 * @param int $year The year for which the iCalendar file should be generated.
 * @return string The iCalendar content.
 */
function generateICalendar(int $year): string
{
    // Set the timezone (adjust if needed), e.g. 'Europe/London' or 'UTC'
    date_default_timezone_set('Pacific/Auckland');

    // Start the iCalendar content
    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//Your Company//NONSGML v1.0//EN\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";

    // Loop through each month of the year
    for ($month = 1; $month <= 12; $month++) {
        // Calculate the payday dates
        $paydays = [
            getAdjustedDate($year, $month, 15),
            getAdjustedDate($year, $month, ($month == 2 ? (isLeapYear($year) ? 29 : 28) : 30))
        ];

        foreach ($paydays as $payday) {
            // Add payday to iCalendar
            $ical .= createEvent($payday, "Payday");

            // Calculate timesheet deadline two days before payday
            $timesheetDeadline = getAdjustedDate($payday->format('Y'), $payday->format('m'), $payday->format('d') - 2);
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
 * Function to adjust the given date to the previous Friday if it falls on a weekend.
 *
 * @param int $year The year of the date.
 * @param int $month The month of the date.
 * @param int $day The day of the date.
 * @return DateTime The adjusted date.
 */
function getAdjustedDate(int $year, int $month, int $day): DateTime
{
    $date = new DateTime("$year-$month-$day");

    // Adjust to the previous Friday if the date falls on a weekend
    if ($date->format('N') >= 6) { // Saturday = 6, Sunday = 7
        $date->modify('last Friday');
    }

    return $date;
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

// Generate the iCalendar content
$icalContent = generateICalendar($year);

// Define the filename for the .ical file
$filename = "paydays_$year.ics";

// Output the iCalendar file as a download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $icalContent;
