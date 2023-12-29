<?php

namespace App\Service;

class Utils
{
    /**
     * @param null|string $bool 
     * @return null|bool 
     */
    public static function strToBoolean(?string $bool): ?bool
    {
        return $bool === 'true' ? true : ($bool === 'false' ? false : null);
    }

    public static function floatify($data)
    {
        if (isset($data)) {
            return floatval($data);
        }
        return 0;
    }

    public static function formatFileName(string $str): string
    {
        $str = Utils::removeAccents($str);
        $str = preg_replace("/[\s]/", '-', $str);
        $str = preg_replace("/['.:%]/", '', $str);
        $str = strtolower($str);
        return $str;
    }

    public static function removeAccents($str)
    {

        if (!preg_match('/[\x80-\xff]/', $str)) return $str;
        return preg_replace("/[?]/", '', utf8_decode($str));

        /**
         * $str = strtr(
         *   utf8_decode($str), 
         *   utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 
         *   'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
         * ); 
         */
    }
}
