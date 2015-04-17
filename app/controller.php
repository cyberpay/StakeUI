<?php
class Controller {
    
    public function __construct() 
    { 
        if (!isset($_SESSION['reddex']) || (time()-$_SESSION['expire']) >= 600) {
            $_SESSION['expire'] = time();
            $_SESSION['reddex'] = $this->reddex('http://reddex.tk/api/v1/')['data'];
            $_SESSION['vers'] = $this->reddex('http://reddex.tk/api/stakeUI/')['ver'];
        }
    }
    
    function humanTiming($time)
    {
        $time = time() - $time;

        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second');

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':''). ' ago';
        }
        return 'Right now';
    }
    
    public function seconds2human($ss) 
    {
        $s = $ss%60;
        $m = floor(($ss%3600)/60);
        $h = floor(($ss%86400)/3600);
        $d = floor(($ss%2592000)/86400);
        $M = floor($ss/2592000);

        return "$M months, $d days, $h hours, $m minutes, $s seconds";
    }
    
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    
    public function get_percentage($total, $number, $formatting)
    {
        return ($total > 0) ? round($number / ($total / 100), $formatting) : 0;
    }
    
    // PRIVATE METHODS
    
    private function reddex($url) 
    {
        return json_decode(file_get_contents($url), true);
    }
}