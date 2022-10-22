<?php
namespace Pigitools\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Réfère à la table Mail
 * @property integer $id
 * @property string $mailFrom
 * @property string $mailTo
 * @property string $mailCC
 * @property string $mailBCC
 * @property string $mailSubject
 * @property string $mailBody
 * @property array $files
 * @property string $info
 * @property boolean $isError
 * @property boolean $isReceive
 * @property string $foreignKey
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package Pigitools\Models\Entities
 * @mixin Builder
 */
class MailModel extends Eloquent
{
    protected $connection = 'litige';
	protected $table      = 'mail';
	protected $primaryKey = 'id';
	public $timestamps    = true;

    public function __construct() {
        parent::__construct();
        $this->table = DB::connection($this->connection)->getDatabaseName() . '.' . $this->getTable();
    }

	protected $fillable = [
        'mailFrom',
        'mailTo',
        'mailCC',
        'mailBCC',
        'mailSubject',
        'mailBody',
        'files',
        'info',
        'isError',
        'isReceive',
        'foreignKey' // Contient l'id de la foreign key mais dynamic du coup le champ doit être préfixé (ex: si lié à un BL alors foreignKey => BL{idBl} )
    ];
}
