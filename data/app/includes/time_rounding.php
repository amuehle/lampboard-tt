<?php

function roundTimeToNearestQuarter(DateTime $dt): DateTime
{
    $minutes = (int)$dt->format('i');
    $hour = (int)$dt->format('H');

    // nearest quarter
    $rounded = round($minutes / 15) * 15;

    if ($rounded === 60) {
        $dt->setTime($hour + 1, 0);
    } else {
        $dt->setTime($hour, $rounded);
    }

    return $dt;
}

function applyTimeRules(string $datetime, string $action, array $employee): string
{
    $dt = new DateTime($datetime);
    $time = (int)$dt->format('H') * 60 + (int)$dt->format('i');

    $roundingEnabled = (int)($employee['excel_rounding_enabled'] ?? 0);
    $janitor = (int)($employee['janitor_exception'] ?? 0);

    // Janitor-Rule: 9:00 AM only when NOT Janitor-Exception
    if ($action === 'come') {

        // AM
        if (!$janitor && $time < 9 * 60) {
            $dt->setTime(9, 0);
        }

        // PM (with Janitor)
        if ($time < 13 * 60 && $time >= 12 * 60) {
            $dt->setTime(13, 0);
        }
    }

    if ($action === 'go') {

        // 11:45–12:15 => 12:00
        if ($time >= 11 * 60 + 45 && $time <= 12 * 60 + 15) {
            $dt->setTime(12, 0);
        }

        // 15:45–16:15 => 16:00
        if ($time >= 15 * 60 + 45 && $time <= 16 * 60 + 15) {
            $dt->setTime(16, 0);
        }
    }

    // Quarter-Round only when enabled
    if ($roundingEnabled) {

        $t = (int)$dt->format('H') * 60 + (int)$dt->format('i');

        // Only specific times
        if (
            ($t >= 9 * 60 && $t <= 11 * 60 + 45) ||
            ($t >= 13 * 60 && $t <= 15 * 60 + 45)
        ) {
            $dt = roundTimeToNearestQuarter($dt);
        }
    }

    return $dt->format('Y-m-d H:i:s');
}