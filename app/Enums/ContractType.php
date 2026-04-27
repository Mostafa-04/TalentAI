<?php

namespace App\Enums;

enum ContractType: string
{
    case CDI = 'CDI';
    case CDD = 'CDD';
    case Freelance = 'Freelance';
    case Stage = 'Stage';
}
