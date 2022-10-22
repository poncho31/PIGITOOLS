<?php

namespace Pigitools\Sources\Logs;

//use Pigitools\Events\BroadcastEventToUserNow;
use Pigitools\Models\Logs;

class Log{

    public static function exception($application, $e, $addErrorMessage= '')
    {
        $debug = debug_backtrace();
        $function = $debug[1]['function'] == 'LogMessages' ? $debug[2]['function'] : $debug[1]['function'];
        $file = $debug[1]['function'] == 'LogMessages' ? $debug[1]['file'] : $debug[0]['file'];
        $line = $debug[1]['function'] == 'LogMessages' ? $debug[1]['line'] : $debug[0]['line'];

        $log = new Logs;
		$log->application =$application;
		$log->methode = $function;
		$log->message = $e->getMessage() . json_encode($addErrorMessage);
		$log->fichier = $file;
		$log->exception = $e->__toString();
		$log->ligne = $line;
        $log->erreur = true;
		$log->date = date('Y-m-d H:i:s');
		$log->save();


		self::dump($log->message);
//        (new BroadcastEventToUserNow())->broadcastEventSend('log', "event.it", ['info'=>"LOG EXCEPTION $log->id: $log->message"], true, false);
    }

    public static function error($application, $errorMessage)
    {
        $debug = debug_backtrace();
        $function = $debug[1]['function'] == 'LogMessages' ? $debug[2]['function'] : $debug[1]['function'];
        $file = $debug[1]['function'] == 'LogMessages' ? $debug[1]['file'] : $debug[0]['file'];
        $line = $debug[1]['function'] == 'LogMessages' ? $debug[1]['line'] : $debug[0]['line'];

        $log = new Logs;
		$log->application =$application;
		$log->methode = $function;
		$log->message = $errorMessage;
		$log->fichier = $file;
		$log->exception = null;
		$log->ligne = $line;
        $log->erreur = true;
		$log->date = date('Y-m-d H:i:s');
		$log->save();

		self::dump($log->message);
//        (new BroadcastEventToUserNow())->broadcastEventSend('log', "event.it", ['info'=>"LOG ERREUR $log->id: $log->message"], true, false);
    }

    public static function monitor($application, $message)
    {
        $debug = debug_backtrace();
        $function = $debug[1]['function'] == 'LogMessages' ? $debug[2]['function'] : $debug[1]['function'];
        $file = $debug[1]['function'] == 'LogMessages' ? $debug[1]['file'] : $debug[0]['file'];

        $log = new Logs;
		$log->application =$application;
		$log->methode = $function;
		$log->message = $message;
		$log->fichier = $file;
		$log->exception = null;
		$log->ligne = null;
        $log->erreur = false;
		$log->date = date('Y-m-d H:i:s');
		$log->save();
		self::dump($log->message);
    }

	public static function dump($message){
		if(config('pigitools.debug_api')){
			dump($message);
		}
	}
}
