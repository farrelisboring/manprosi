<?php

namespace App\Enums;

enum TrackingEventType: string
{
    case Detected = 'detected';
    case Entered = 'entered';
    case Exited = 'exited';
    case Moved = 'moved';
}
