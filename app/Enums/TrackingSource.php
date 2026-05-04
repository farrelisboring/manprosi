<?php

namespace App\Enums;

enum TrackingSource: string
{
    case Rfid = 'rfid';
    case Barcode = 'barcode';
    case QrCode = 'qr_code';
    case Manual = 'manual';
}
