<?php

function my_events_calendar_format_event_date($date) {
    // Assuming $date is in the format 'YYYY-MM-DD'
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
    if ($dateTime) {
        return $dateTime->format('Y-m-d\TH:i:s');
    }
    return $date; // Return original if conversion fails
}