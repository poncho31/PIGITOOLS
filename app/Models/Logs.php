<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 16 Dec 2019 12:56:02 +0000.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Logs
 *
 * @property int $id
 * @property string $application
 * @property string $methode
 * @property string $message
 * @property Carbon $date
 * @property bool $erreur
 * @property string $fichier
 * @property string $exception
 * @property int $ligne
 *
 * @package App\Models\Entities
 * @mixin Builder
 */
class Logs extends Eloquent
{
	protected $table = 'logs';
	protected $primaryKey = 'id';
	public $timestamps = false;

	protected $fillable = [
		'application',
		'methode',
		'message',
		'date',
		'erreur',
		'fichier',
		'exception',
		'ligne',
	];
}
