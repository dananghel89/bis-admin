<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class ConvertTablesToInnodb extends Migration
{
    /**
     * Tables created before config/database.php set 'engine' => 'InnoDB'.
     *
     * @var array
     */
    protected $tables = [
        'users', 'password_resets', 'options', 'infos', 'alerts', 'plans', 'licenses', 'migrations',
    ];

    /**
     * Run the migrations.
     *
     * MyISAM has no transactions and no foreign keys; MySQL only.
     *
     * @return void
     */
    public function up()
    {
        $this->setEngine('InnoDB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setEngine('MyISAM');
    }

    /**
     * Change the storage engine of every existing table in the list.
     *
     * @param string $engine
     * @return void
     */
    protected function setEngine($engine)
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE = {$engine}");
            }
        }
    }
}
