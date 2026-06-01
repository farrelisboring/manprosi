<?php

namespace App\Enums;

enum GeofenceRuleType: string
{
    case RoomChangeNotification = 'room_change_notification';
    case MustStayInside = 'must_stay_inside';
    case RestrictedEntry = 'restricted_entry';
}
