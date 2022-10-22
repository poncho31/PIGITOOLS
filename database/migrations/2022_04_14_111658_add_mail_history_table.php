<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMailHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_history', function (Blueprint $table) {

            $table->unsignedBigInteger('id');
            $table->string('mailFrom');
            $table->mediumText('mailTo');
            $table->mediumText('mailCC')->nullable()->default(null);
            $table->mediumText('mailBCC')->nullable()->default(null);
            $table->mediumText('mailSubject');
            $table->mediumText('mailBody');
            $table->json('files')->nullable()->comment('Contient un tableau de fichiers encodés en base 64');
            $table->text('info')->nullable();
            $table->boolean('isError')->default(true);
            $table->boolean('isReceive')->default(false)->comment("Est-ce qu'il s'agit d'un mail reçu ou envoyé ?");
            $table->string('foreignKey')->nullable()->comment("Contient l'id de la foreign key mais est dynamic du coup le champ doit être préfixé (ex: si lié à un BL alors foreignKey => BL{idBl} )");
            $table->timestamps();

            $table->index('id','idIndex');
            $table->index('isError', 'isErrorIndex');
            $table->index('isReceive','isReceiveIndex');
            $table->index('foreignKey','foreignKeyIndex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_history');
        Schema::table('mail_history', function (Blueprint $table) {
            $table->dropIndex('idIndex');
            $table->dropIndex('isErrorIndex');
            $table->dropIndex('isReceiveIndex');
            $table->dropIndex('foreignKeyIndex');
        });
    }
}
