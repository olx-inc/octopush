<?php

namespace Models;

class Version
{    
    const MAJOR ="0";
    const MINOR ="9";
    const BUILD ="[build]";
    const REVISION ="[revision]";

    static function getFull()
    {
        return Version::MAJOR . "." . Version::MINOR . "." . Version::REVISION . "." . Version::BUILD;
    }

    static function getShort()
    {
    	return Version::MAJOR . "." . Version::MINOR;
    }
 }
