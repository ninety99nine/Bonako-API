<?php

namespace App\Http\Resources\Helpers;

use Illuminate\Support\Str;

class ResourceLink {

    public $name;
    public $href;

    public function __construct($name, $href)
    {
        $this->name = $name;
        $this->href = $href;
    }

    /**
     *  Return the link structure for
     *  external API consumption
     */
    public function getLink()
    {
        return [
            Str::replace('.', '_', $this->name) => $this->href
        ];
    }
}
