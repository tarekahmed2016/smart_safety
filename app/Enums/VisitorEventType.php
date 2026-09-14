<?php

namespace App\Enums;

enum VisitorEventType: string
{
    case Visitor = 'visitor';
    case Visit = 'visit';
    case PageView = 'page_view';
}
