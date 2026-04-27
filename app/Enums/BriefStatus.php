<?php

namespace App\Enums;

enum BriefStatus: string
{
    case Draft      = 'draft';
    case Active     = 'active';
    case Sourcing   = 'sourcing';
    case Interviews = 'interviews';
    case Closed     = 'closed';
}