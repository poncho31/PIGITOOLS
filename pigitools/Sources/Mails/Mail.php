<?php

namespace Pigitools\Sources\Mails;

use Pigitools\Models\MailModel;
use Pigitools\Sources\Converter\Converter;
use Pigitools\Sources\Logs\Log;
use PhpImap\Mailbox;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\MockObject\Stub\Exception;
use Throwable;
use function PHPUnit\Framework\throwException;

class Mail {
    private static array $dataMailError = [];

    /**
     * Envoi du mail (from, to, cc, bcc,subject, body, files)
     * @param string $mailFrom
     * @param string|array $mailTo
     * @param string|array $mailCC
     * @param string|array $mailBCC
     * @param string $mailSubject
     * @param string $mailBody
     * @param string|array|null $files
     * @param string|null $typeMailName
     * @return bool
     */
     public static function send(string $mailFrom, $mailTo, $mailCC, $mailBCC, string $mailSubject, string $mailBody, $files = null, ?string $typeMailName = null) : bool
    {
        self::getDataMailError(get_defined_vars());
        // Send mail
        try{
            $mail = new PHPMailer(true);
            $from = self::mail_info($mailFrom); // Info : pass / mail / smtp / port
            $mailTo = self::mail_parser($mailTo);
            $mailCC = self::mail_parser($mailCC);
            $mailBCC = self::mail_parser($mailBCC);

            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                         // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->SMTPAuth   = $from['smtp'];                          // Enable SMTP authentication
            $mail->Host       = $from['host'];                          // Set the SMTP server to send through
            $mail->Username   = $from['mail'];                          // SMTP username
            $mail->Password   = $from['pass'];                          // SMTP password
            $mail->Port       = $from['port'];                          // TCP port to connect to

            // FROM
            $mail->setFrom($from['mail'], $from['name']);
            $mail->addReplyTo($from['replyTo'], 'Test');

            // TO
            foreach ($mailTo as $to) {
                $mail->addAddress($to);
            }

            // CC
            foreach ($mailCC as $to) {
                $mail->addCC($to);
            }

            // BCC
            foreach ($mailBCC as $to) {
                $mail->addBCC($to);
            }

            // Attachments
            $files = !is_array($files)?($files == null ? [] : [$files]):$files;
            foreach ($files as $file) {
                $filename = str_replace('.tmp',".".Converter::MimetypeExtension(mime_content_type($file), false), $file); // find real file extension for base64 file decode with .tmp extension
                $mail->addAttachment($file, basename($file));
            }

            // Content
            $mail->isHTML(true);                                // Set email format to HTML
            $mail->CharSet = 'UTF-8';
            $mail->Subject = (config('pigitools.debug') ? "TEST DEBUG "  :  ""). $mailSubject;
            $mail->Body    = $mailBody;
            $isMailSend = $mail->Send();

            try{
                (new MailModel)->updateOrCreate(self::$dataMailError,array_merge(self::$dataMailError, ['isError'=>!$isMailSend]));
            }
            catch(\Exception $e){
                dump($e->getMessage());
                Log::exception('MAIL-INSERT-MAILTABLE', $e);
            }
            return true;
        }
        catch(\Exception $e){
            Log::exception('MAIL', $e);
            //dump($e->getMessage());
            // SAVE MAIL IF ERROR
            (new MailModel)->updateOrCreate(self::$dataMailError,self::$dataMailError);
            return false;
        }
    }

    /**
     * Garde les données du mails a envoyer dans la variable $dataMailError (si erreur alors mail peut être renvoyé => fichier stocké en DB en base 64)
     * @param array $data
     * @return void
     */
    private static function getDataMailError(array $data): void
    {
        $files = is_array($data['files'])?$data['files']: array_filter([$data['files']]);
        $base64encodeFiles = [];
        foreach($files as $file){
            $base64encodeFiles[] = base64_encode(file_get_contents($file));
        }
        $data['mailTo'] = is_array($data['mailTo'])?implode(';', $data['mailTo']) : $data['mailTo'];
        $data['mailCC'] = is_array($data['mailCC'])?implode(';', $data['mailCC']) : $data['mailCC'];
        $data['mailBCC'] = is_array($data['mailBCC'])?implode(';', $data['mailBCC']) : $data['mailBCC'];
        $data['files'] = json_encode($base64encodeFiles);
        $data['info'] = $data['typeMailName'];
        unset($data['typeMailName']);
        self::$dataMailError = $data;
    }

    /**
     * Ajoute les mails reçu dans la table Mail (gestion des erreurs)
     * @param array $data
     * @param array $checkUpdateOrCreate
     * @return bool
     */
    public function archiveMailReceive(array $data, array $checkUpdateOrCreate): bool
    {
        try{
            self::getDataMailError($data);
            (new MailModel)->updateOrCreate($checkUpdateOrCreate,array_merge(self::$dataMailError, ['isReceive'=>true]));
            return true;
        }
        catch(Throwable $e){
            Log::exception('MAIL-ARCHIVE-RECEIVE',$e);
            return false;
        }
    }

    /**
     * Renvoi les mails en erreur
     * @return void
     */
    public function sendError(): void
    {
        foreach((new MailModel)->where('isError', 1)->get() as $mailError){
            $data = $mailError->toArray();
            $filesBase64ToPath = [];
            foreach(json_decode($data['files']) as $fileBase64){
                $tempFileName = tempnam(storage_path('temp'), 'MAIL');
                file_put_contents($tempFileName,base64_decode($fileBase64, true) );
                $filesBase64ToPath[] =$tempFileName;
            }

            $isSendMail = self::send($data['mailFrom'], $data['mailTo'], $data['mailCC'], $data['mailBCC'],  $data['mailSubject'],  $data['mailBody'], $filesBase64ToPath);
            if($isSendMail){
                $mailError->update(['isError'=>0]);
                dump("Mail {$mailError->id} renvoyé");
            }
        }
    }

    /**
     * Renvoi les infos de connexion pour un mail donné ou un alias de mail (ex:ada)
     * @param $from
     * @return array
     */
    private static function mail_info($from): array
     {
        $data = [
            'mail'=> null,
            'pass'=> null,
            'name'=> null,
            'smtp'=> true,
            'port'=> 587,
            'host'=> 'smtp.office365.com',
            'replyTo'=> null
        ];
         return array_merge($data, config("mail.accounts.$from", []));
    }


    /**
     * Converti les adresses mails séparées par ; en string[] et ajoute la boite mail test en copie
     * @param array|string|null $mail
     * @return array
     */
    private static function mail_parser(array|string|null $mail): array
     {
        $mailDebugOrProd = config('pigitools.debug') ? config('mail.to.test'): $mail; // Si app_debug true alors envoie à test@test.com
        $arrayMail = array_merge((
            !is_array($mailDebugOrProd)?explode(';',$mailDebugOrProd): $mailDebugOrProd),
            [config('mail.to.test')] // Ajout du mail test@test.com dans tous les cas
        );
        return array_filter(array_map('trim', $arrayMail));
    }


    /**
     * Retourne une vue (blade) pour le body du mail
     * @param string $view
     * @param array $data
     * @return string
     * @throws Throwable
     */
    public static function render(string $view, array $data = []): string
    {
         return view($view, $data)->render();
    }


    /**
     * @param string $mailboxname
     * @param string $specificFolder
     * @return Exception|Mailbox|null
     */
    public function getOutlook(string $mailboxname, string $specificFolder = ''): Exception|Mailbox|null
    {
        $mailbox = null;
        try {
            if($mailboxname == env('MAIL_BOX_NAME_OUTLOOK')){
                $mailbox = new Mailbox(env('MAIL_OUTLOOK_IMAP')?? '{outlook.office365.com:993/imap/ssl}'.$specificFolder, env('MAIL_OUTLOOK'), env('MAIL_PASS_OUTLOOK'));
            }
            return $mailbox;

        } catch (\Exception $e) {
            return throwException($e);

        }
    }
}
