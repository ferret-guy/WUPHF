<?php

namespace App\Providers;

use Illuminate\Auth\GenericUser;

class WuphfUser extends GenericUser
{
    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }
}
