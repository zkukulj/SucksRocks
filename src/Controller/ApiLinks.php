<?php

namespace App\Controller;

class ApiLinks
{
    public function getUrl($from)
    {
        switch(strtolower($from)){
            case "github":
                return "https://api.github.com/search/issues?q=";
            case "twitter":
                return "https://api.twitter.com/1.1/search/tweets.json?q=";
            default:
                return "https://api.github.com/search/issues?q=";
        }

    }
}