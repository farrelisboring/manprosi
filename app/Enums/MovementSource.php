<?php

namespace App\Enums;

enum MovementSource: string
{
    case Manual = 'manual';
    case Rfid = 'rfid';
    case Barcode = 'barcode';
    case QrCode = 'qr_code';
    case System = 'system';
}
