<?php

namespace App\Helpers;


class PlatformManager
{
    public string $platform;

    public function __construct()
    {
        //  Get the platform e.g web, ussd or mobile
        $this->platform = strtolower(request()->header('X-Platform'));
    }

    public function isWeb() {
        return $this->platform == 'web';
    }

    public function isUssd() {
        return $this->platform == 'ussd';
    }

    public function isMobile() {
        return $this->platform == 'mobile';
    }
}
