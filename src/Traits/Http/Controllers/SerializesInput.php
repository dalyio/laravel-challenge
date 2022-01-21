<?php

namespace Dalyio\Challenge\Traits\Http\Controllers;

trait SerializesInput
{
    /**
     * @return void
     */
    protected function serialize($input)
    {
        // Security checks here
        
        return $input;
    }
}
