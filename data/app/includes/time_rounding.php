
<?php

function roundTimeToNearestQuarter(DateTime $dt): DateTime
{
    $minutes = (int)$dt->format('i');
    $hour = (int)$dt->format('H');

    // Round to the nearest quarter-hour
    $rounded = round($minutes / 15) * 15;

    if ($rounded === 60) {
        $dt->setTime($hour + 1, 0);
    } else {
        $dt->setTime($hour, (int)$rounded);
    }

    return $dt;
}

function applyTimeRules(
    string $datetime,
    string $action,
    array $employee
): string {
    $dt = new DateTime($datetime);

    $time = (int)$dt->format('H') * 60
          + (int)$dt->format('i');

    $roundingEnabled = (int)(
        $employee['excel_rounding_enabled'] ?? 0
    );

    $janitor = (int)(
        $employee['janitor_exception'] ?? 0
    );

    // Clock-in rules
    if ($action === 'come') {

        // AM rule
        if ($time < 9 * 60) {

            if ($janitor) {
                // Janitor: minimum start time is 08:00
                if ($time < 8 * 60) {
                    $dt->setTime(8, 0);
                } else {
                    // Janitor: round to nearest 15 minutes
                    // between 08:00 and 09:00
                    $dt = roundTimeToNearestQuarter($dt);
                }
            } else {
                // Other employees: start at 09:00
                $dt->setTime(9, 0);
            }
        }

        // PM rule: 12:00–12:59 becomes 13:00
        if ($time < 13 * 60 && $time >= 12 * 60) {
            $dt->setTime(13, 0);
        }
    }

    // Clock-out rules
    if ($action === 'go') {

        // 11:45–12:15 becomes 12:00
        if (
            $time >= 11 * 60 + 45 &&
            $time <= 12 * 60 + 15
        ) {
            $dt->setTime(12, 0);
        }

        // 15:45–16:15 becomes 16:00
        if (
            $time >= 15 * 60 + 45 &&
            $time <= 16 * 60 + 15
        ) {
            $dt->setTime(16, 0);
        }
    }

    // Optional quarter-hour rounding
    if ($roundingEnabled) {

        $t = (int)$dt->format('H') * 60
           + (int)$dt->format('i');

        // Only specific time windows
        if (
            ($t >= 9 * 60 && $t <= 11 * 60 + 45) ||
            ($t >= 13 * 60 && $t <= 15 * 60 + 45)
        ) {
            $dt = roundTimeToNearestQuarter($dt);
        }
    }

    return $dt->format('Y-m-d H:i:s');
}
