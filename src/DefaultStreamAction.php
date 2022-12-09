<?php

namespace Tonysm\TurboLaravel;

enum DefaultStreamAction: string
{
    case APPEND = 'append';
    case PREPEND = 'prepend';
    case UPDATE = 'update';
    case REPLACE = 'replace';
    case BEFORE = 'before';
    case AFTER = 'after';
    case REMOVE = 'remove';
}
