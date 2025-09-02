<?php

namespace App\Enums;

enum TaskType: string
{
    case STUDY = 'study';
    case WORK = 'work';
    case QUESTION = 'question';
    case QUICK_NOTE = 'quick_note';
    case REMINDER = 'reminder'; // corrigi o typo "remimber"
    case HOUSE = 'house';
    case RANDOM = 'random';
}
