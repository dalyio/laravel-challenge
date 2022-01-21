<?php

namespace Dalyio\Challenge\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CodeblockStub
{   
    /**
     * @var string
     */
    private $directory = '/storage/stubs';
    
    /**
     * @param string $search
     * @return \Dalyio\Challenge\Models\Geo\Zipcode[]
     */
    public function get($key)
    {
        $filepath = $this->filepath($key);
        $snippet = File::get($filepath);
        if ($snippet) highlight_string($snippet);
    }
    
    protected function filepath($key)
    {
        return realpath(base_path($this->directory).DIRECTORY_SEPARATOR.$key.'.stub');
    }
}
