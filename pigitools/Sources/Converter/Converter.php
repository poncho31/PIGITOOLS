<?php


namespace Pigitools\Sources\Converter;


use Carbon\Carbon;
use DateTime;
use Exception;

class Converter
{
    /**
     * @description Convert object to array / multidimensionnal array
     * @param object|array|string $obj
     * @return array|string
     */
    public static function object_to_array(object|array|string $obj): array|string
    {
        if(is_object($obj) || is_array($obj)) {
            $ret = (array) $obj;
            foreach($ret as &$item) {
                $item = self::object_to_array($item);
            }
            return $ret;
        }
        else {
            return (string)$obj;
        }
    }

    public static function flat_array($array): array
    {
        static $newArray = [];
        foreach ($array as $key => $value) {
            if(is_array($value)) {
                self::flat_array($value);
            } else {
                $newArray[$key] = $value;
            }
        }
        return $newArray;
    }

    public static function array_map_recursive(callable $func, array $array) {
        $array = Converter::object_to_array($array);
        return filter_var($array, \FILTER_CALLBACK, ['options' => $func]);
    }

    public static function string($val): string
    {
        return strval($val);
    }

    public static function decimal($val): float
    {
        return floatval($val);
    }

    public static function integer($val): int
    {
        return intval($val);
    }

    /**
     * @throws Exception
     */
    public static function date($val, $type = 'date', $format ='Y-m-d'): DateTime|Carbon|string
    {
        $carbonDate = Carbon::parse($val);
        if($type =='date'){
            return $carbonDate;
        }
        elseif ($type=='string'){
            return $carbonDate->format($format);
        }
        elseif ($type=='strtotime'){
            return new DateTime(date($format, strtotime($val)));
        }
        else{
            return $carbonDate;
        }
    }

    public static function file($file){
        return (null !== $file)?$file:[];
    }

    public static function csv_to_array($filename='', $delimiter=',', $withHeader = false): bool|array|\stdClass
    {
        if(!file_exists($filename) || !is_readable($filename)){
            return false;
        }

        $data = [];
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
            {
                $data[] = $row;
            }
            fclose($handle);
        }


        if(!$withHeader){
            unset($data[key($data)]);
            return $data;
        }
        else{
            $obj = new \stdClass();
            $obj->header = $data[key($data)];
            unset($data[key($data)]);
            $obj->lines = $data;
            return $obj;
        }
    }

    public static function csv($csv_or_path, $delimiter = ",",$isPath = true, $skip_empty_lines = true, $trim_fields = true): array
    {
        $csv_string = $isPath? file_get_contents($csv_or_path) : $csv_or_path;

        $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
        $enc = preg_replace_callback(
            '/"(.*?)"/s',
            function ($field) {
                return urlencode(utf8_encode($field[1]));
            },
            $enc
        );
        $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
        return array_map(
            function ($line) use ($delimiter, $trim_fields) {
                $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
                return array_map(
                    function ($field) {
                        return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                    },
                    $fields
                );
            },
            $lines
        );
    }


    /**
     * @description Recursive implode()
     * @param array $array
     * @param string $glue
     * @param false $include_keys
     * @param bool $trim_all
     * @return string
     */
    public static function recursive_implode(array $array, string $glue = ',', bool $include_keys = false, bool $trim_all = true): string
    {
        $glued_string = '';

        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, function($value, $key) use ($glue, $include_keys, &$glued_string)
        {
            $include_keys and $glued_string .= $key.$glue;
            $glued_string .= $value.$glue;
        });

        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));

        // Trim ALL whitespace
        $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);

        return (string) $glued_string;
    }

    public static function stripAccents($stripAccents): string
    {
        return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public static function MimetypeExtension($mime, $flip = true): bool|int|string
    {
        $mime_map = [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'application/x-compressed' => '7zip',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'application/postscript' => 'ai',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'application/macbinary' => 'bin',
            'application/mac-binary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/bmp' => 'bmp',
            'image/x-bmp' => 'bmp',
            'image/x-bitmap' => 'bmp',
            'image/x-xbitmap' => 'bmp',
            'image/x-win-bitmap' => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/ms-bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'application/bmp' => 'bmp',
            'application/x-bmp' => 'bmp',
            'application/x-win-bitmap' => 'bmp',
            'application/cdr' => 'cdr',
            'application/coreldraw' => 'cdr',
            'application/x-cdr' => 'cdr',
            'application/x-coreldraw' => 'cdr',
            'image/cdr' => 'cdr',
            'image/x-cdr' => 'cdr',
            'zz-application/zz-winassoc-cdr' => 'cdr',
            'application/mac-compactpro' => 'cpt',
            'application/pkix-crl' => 'crl',
            'application/pkcs-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'application/pkix-cert' => 'crt',
            'text/css' => 'css',
            'text/x-comma-separated-values' => 'csv',
            'text/comma-separated-values' => 'csv',
            'application/vnd.msexcel' => 'csv',
            'application/x-director' => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'message/rfc822' => 'eml',
            'application/x-msdownload' => 'exe',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gpg-keys' => 'gpg',
            'application/x-gtar' => 'gtar',
            'application/x-gzip' => 'gzip',
            'application/mac-binhex40' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'image/x-ico' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'text/calendar' => 'ics',
            'application/java-archive' => 'jar',
            'application/x-java-application' => 'jar',
            'application/x-jar' => 'jar',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'application/x-javascript' => 'js',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.google-earth.kml+xml' => 'kml',
            'application/vnd.google-earth.kmz' => 'kmz',
            'text/x-log' => 'log',
            'audio/x-m4a' => 'm4a',
            'audio/mp4' => 'm4a',
            'application/vnd.mpegurl' => 'm4u',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/oda' => 'oda',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'font/otf' => 'otf',
            'application/x-pkcs10' => 'p10',
            'application/pkcs10' => 'p10',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/pkcs7-mime' => 'p7c',
            'application/x-pkcs7-mime' => 'p7c',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'application/x-x509-user-cert' => 'pem',
            'application/x-pem-file' => 'pem',
            'application/pgp' => 'pgp',
            'application/x-httpd-php' => 'php',
            'application/php' => 'php',
            'application/x-php' => 'php',
            'text/php' => 'php',
            'text/x-php' => 'php',
            'application/x-httpd-php-source' => 'php',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'application/x-rar' => 'rar',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'application/x-pkcs7' => 'rsa',
            'text/rtf' => 'rtf',
            'text/richtext' => 'rtx',
            'video/vnd.rn-realvideo' => 'rv',
            'application/x-stuffit' => 'sit',
            'application/smil' => 'smil',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/x-gzip-compressed' => 'tgz',
            'image/tiff' => 'tiff',
            'font/ttf' => 'ttf',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'text/vtt' => 'vtt',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/wbxml' => 'wbxml',
            'video/webm' => 'webm',
            'image/webp' => 'webp',
            'audio/x-ms-wma' => 'wma',
            'application/wmlc' => 'wmlc',
            'video/x-ms-wmv' => 'wmv',
            'video/x-ms-asf' => 'wmv',
            'font/woff' => 'woff',
            'font/woff2' => 'woff2',
            'application/xhtml+xml' => 'xhtml',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'text/xsl' => 'xsl',
            'application/xspf+xml' => 'xspf',
            'application/x-compress' => 'z',
            'application/x-zip' => 'zip',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/s-compressed' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-scriptzsh' => 'zsh',
        ];
        $mime_map = $flip ? array_flip($mime_map) : $mime_map;
        return $mime_map[$mime] ?? false;
    }


    public static function convertToHoursMins($time, $format = '%02d heures %02d minutes'): string
    {
        if ($time < 1) {
            return'';
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    /**
     * Filtre un tableau d'objets en fonction d'un filtre
     * @param array $arrayOfObject
     * @param string|string[] $getFilterKey
     * @param bool $getFilterKeyName
     * @return array
     */
    public static function array_object_filter(array $arrayOfObject, array|string $getFilterKey, bool $getFilterKeyName = false): array
    {
        $data = [];
        $getFilterKey = is_string($getFilterKey) ? [$getFilterKey] : $getFilterKey;
        foreach($getFilterKey as $filterName){
            foreach($arrayOfObject as $key => $obj){
                if(str_contains($key, $filterName)){
                    if($getFilterKeyName){
                        $data[$filterName][$key]=$obj;
                    }
                    else{
                        $data[$key]=$obj;
                    }
                }
            }
        }
        return $data;
    }

    public static function sortArrayKeyDate($arr): array
    {
        uksort($arr, function($dt1, $dt2){return strtotime($dt1) - strtotime($dt2);});
        return array_reverse($arr);
    }


    public static function toUtf8(string $data): array|bool|string|null
    {
        return mb_convert_encoding($data, "ISO-8859-1", 'utf-8');
    }


    public static function convertTo($string, $type = 'string', $param = 2): float|int|string|null
    {
        $string = trim($string);
        if($type == 'datetime'){
            $date = date("Y-m-d", strtotime($string));
            return ($date == "1970-01-01")?null:$date;
        }
        elseif($type == 'decimal'){
            $length = strlen($string) - $param;
            return floatval(substr_replace($string, '.', $length, 0));
        }
        elseif($type == 'int'){
            return intval($string);
        }
        else{
            return $string;
        }
    }
}
