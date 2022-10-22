<?php

namespace Pigitools\Sources\Html\Blade;

use Illuminate\Support\Facades\Gate;

class BladeMake
{
    /**
     * Crée un simple formulaire avec un submit avec une url menant vers '/job' par défaut (la classe css 'job' permet de faire ceci de manière asynchrone => voir general.ajaxCallForm)
     **/
    private static function Job(string $displayName ='Exemple',string $jobname ='exemple', string $url ='/job', array $canAccess = [], bool $sendMail = false, bool $isZip = true, string $typeExport = 'xlsx', string $addInputParameters = ''): string
    {
        $jobnameFormat = str_replace([' ', '-'], ['_'], $jobname);
        $sendMail      = $sendMail ? 1 : 0;
        $isZip         = $isZip    ? 1 : 0;
        if(Gate::any($canAccess)){
            return ($addInputParameters!== ''? "<label class='form-group'><h4><b>$displayName</b></h4></label>" : '') .

                "<form class='job' method='POST' action='$url'>
                    <div class='row'>
                        <input type='hidden' name='jobname' id='jobname' value='$jobnameFormat'>
                        <input type='hidden' name='isZip' id='isZip' value='$isZip'>
                        <input type='hidden' name='sendMail' id='sendMail' value='$sendMail' >
                        <input type='hidden' name='typeExport' id='typeExport' value='$typeExport' placeholder='Peut être (1) xlsx ou (2) csv'>
                        $addInputParameters
                    </div>
                    <div class='form-group clearfix'>
                        <input type='submit' value='$displayName' class='w-100 btn btn-success my-1 $jobnameFormat col-sm-12'>
                    </div>
                </form>
                "
                .($addInputParameters != '' ? '<hr>' : '');
        }
        else{
            return '';
        }
    }


    public static function Exemple(): string
    {
        $inputsParameters = '
            <div class="form-group col-md-6 col-sm-6 mb-2">
                <input placeholder="De" class="form-control" name="parameters[dateDebut]" id="dateDebut" type="date" title="Date début">
            </div>';

        return self::Job("Export tableau","tableau", url('/job'), ['admin'], true, true, 'csv', $inputsParameters);
    }
}
