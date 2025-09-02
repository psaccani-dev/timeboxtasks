<?php

namespace App\Enums;



namespace App\Enums;

enum TimeBoxType: string
{
    case FOCUS = 'focus';
    case MEETING = 'meeting';
    case BREAK = 'break';
    case STUDY = 'study';
    case CUSTOM = 'custom';
    case WORK = 'work';
    case HOUSE = 'house';
    case RANDOM = 'random';
}
