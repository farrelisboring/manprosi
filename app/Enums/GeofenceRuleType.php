<?php

namespace App\Enums;

enum GeofenceRuleType: string
{
    case MustStayInside = 'must_stay_inside';
    case RestrictedEntry = 'restricted_entry';
}
