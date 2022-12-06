<?php

use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->ulid('id')->change();
        });

        foreach(User::all() as $user) {
            $user->id = $user->newUniqueId();
            $user->save();
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->ulid('tokenable_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $id = User::count(); // to avoid collisions when resequencing
        foreach(User::all() as $user) {
            $user->id = (string) $id++;
            $user->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->id()->change();
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
};
