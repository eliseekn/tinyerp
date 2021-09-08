<?php

namespace App\Database\Migrations;

use Framework\Database\Migration;

class TokensTable_20210403034738
{         
    /**
     * name of table
     *
     * @var string
     */
    protected $table = 'tokens';

    /**
     * create table
     *
     * @return void
     */
    public function create(): void
    {
        Migration::newTable($this->table)
            ->addBigInt('id')->primaryKey()
            ->addString('email')
            ->addString('token')->unique()
            ->addTimestamp('expire')->nullable()
            ->create();
    }
    
    /**
     * drop table
     *
     * @return void
     */
    public function drop(): void
    {
        Migration::dropTable($this->table);
    }
}
