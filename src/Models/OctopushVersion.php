<?php

namespace Models;

class OctopushVersion
{
    const MAJOR ="1";
    const MINOR ="0";
    const BUILD ="[build]";
    const REVISION ="[revision]";


    public static function getFull()
    {
        return OctopushVersion::MAJOR . "." . OctopushVersion::MINOR . "." . OctopushVersion::REVISION . "." . OctopushVersion::BUILD;
    }

    public static function getShort()
    {
        return OctopushVersion::MAJOR . "." . OctopushVersion::MINOR;
    }
 }
