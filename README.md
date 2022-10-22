
# PIGITOOLS
## Sources PHP
### - **Mails :**
    send(
        string 
        $mailFrom, 
        $mailTo, 
        $mailCC, 
        $mailBCC, 
        string $mailSubject, 
        string $mailBody, 
        $files = null, 
        ?string $typeMailName = null
    )

### - **Logs :**
    - exception()
    - error()
    - monitor()

### - **Converter :**
    - object_to_array(object|array|string $obj)
    - flat_array($array)
    - date($val, $type = 'date', $format ='Y-m-d')
    - csv_to_array($filename='', $delimiter=',', $withHeader = false)
    - sortArrayKeyDate($arr)
    - convertTo($string, $type = 'string', $param = 2)
    - toUtf8(string $data)

### - **Html :**
#### BladeMake :
    - Job(string $displayName ='Exemple',string $jobname ='exemple', string $url ='/job', array $canAccess = [], bool $sendMail = false, bool $isZip = true, string $typeExport = 'xlsx', string $addInputParameters = '')
