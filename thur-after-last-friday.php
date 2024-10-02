<?php
// Define the year you want to generate the paydays for
$year = 2024; // Change this to the desired year

// Set the timezone (GMT / London)
$timezone = 'Europe/London';

// Set headers to download the icalendar file with the year in the filename
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="payday-' . $year . '.ics"');

// Get the current date
$today = new DateTime('now', new DateTimeZone($timezone));
$currentYear = (int)$today->format('Y');

// Determine the start date based on the current year
if ($year == $currentYear) {
    $startDate = $today; // Start from today if it's the current year
} else {
    $startDate = new DateTime("$year-01-01", new DateTimeZone($timezone)); // Start from Jan 1 for future years
}

// Set the end date for generating events (always Dec 31)
$endDate = new DateTime("$year-12-31", new DateTimeZone($timezone));

// Start building the iCalendar data
$ical = "BEGIN:VCALENDAR\r\n";
$ical .= "VERSION:2.0\r\n";
$ical .= "PRODID:-//Your Company//Pay Day Calendar//EN\r\n";
$ical .= "CALSCALE:GREGORIAN\r\n";
$ical .= "METHOD:PUBLISH\r\n";
$ical .= "X-WR-TIMEZONE:$timezone\r\n";

// Function to calculate the Thursday after the last Friday of a month
function getPayDay($year, $month, $timezone) {
    $lastFriday = new DateTime("last Friday of $year-$month", new DateTimeZone($timezone));
    $lastFriday->modify('next Thursday');
    return $lastFriday;
}

// Loop through each month and add the payday events as all-day events
for ($month = 1; $month <= 12; $month++) {
    $payDay = getPayDay($year, $month, $timezone);
    
    // Ensure that we only include events starting from the current date if it's the current year
    if ($payDay >= $startDate) {
        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "SUMMARY:Pay day\r\n";
        $ical .= "DTSTART;VALUE=DATE:" . $payDay->format('Ymd') . "\r\n"; // All-day event
        $ical .= "DTEND;VALUE=DATE:" . $payDay->modify('+1 day')->format('Ymd') . "\r\n"; // Next day to indicate end of the all-day event
        $ical .= "DESCRIPTION:Pay day\r\n";
        $ical .= "STATUS:CONFIRMED\r\n";
        $ical .= "END:VEVENT\r\n";
    }
}

// Close the calendar
$ical .= "END:VCALENDAR\r\n";

// Output the iCalendar data
echo $ical;
?>
