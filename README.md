# PHP generate paydays iCal

Generate iCalendar (.ics) file of payday deadlines and related timesheet deadlines


## Requirements

This PHP script generates an iCalendar (.ics) file that adheres to the following requirements:

* Generates paydays on the 15th and 30th of each month (or the 28th/29th for February).
* Moves paydays to the preceding Friday if they fall on a weekend.
* Creates timesheet deadlines two days before each payday, and also moves them to the preceding Friday if they fall on a weekend.
* Allows the user to set the year for which the iCalendar events should be generated.


## How the script works

Function `generateICalendar`: This is the main function that loops through each month of the specified year to generate the paydays and their corresponding timesheet deadlines.

Function `createEvent`: This function creates an iCalendar event for the given date with a summary description ("Payday" or "Timesheet Deadline").

Function `getAdjustedDate`: This function adjusts a date to the previous Friday if it falls on a weekend.

Function `isLeapYear`: Checks if the year is a leap year, which is necessary for determining the correct number of days in February.

Setting the Year: You can change the `$year` variable to generate the iCalendar for a different year.

Downloading the file: The script outputs the generated iCalendar content and prompts the user to download it as a `.ics` file.


## Usage

1. Save the script as `generate_paydays.php`.
2. Run it on a server with PHP 8.2+ installed.
3. When accessed via a web browser, it will generate the `.ics` file for download.