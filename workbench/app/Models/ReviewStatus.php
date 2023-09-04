<?php

namespace Workbench\App\Models;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getKey()
    {
        return $this->value;
    }
}
