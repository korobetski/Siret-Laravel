<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('siret')->unique();
            $table->integer('siren');
            $table->string('tva', 20);
            $table->string('nom', 255);

            $table->integer('numeroVoie')->nullable();
            $table->string('typeVoie', 20)->nullable();
            $table->string('libelleVoie', 50);
            $table->integer('codePostal');
            $table->string('libelleCommune', 50);

            $table->date('dateCreation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entreprises');
    }
};
