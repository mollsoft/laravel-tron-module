<?php

namespace Mollsoft\LaravelTronModule\Enums;

enum TronTransactionType: string
{
    case INCOMING = 'in';
    case OUTGOING = 'out';
}
