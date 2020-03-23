<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create{{tableName}}Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{tableName}}', function (Blueprint $table) {
            $table->increments('id');

{{otherFieldsDefinition}}

            $table->dateTime('createdAt')->comment('创建时间')->nullable();
            $table->dateTime('updatedAt')->comment('最近更新时间')->nullable();
            $table->dateTime('deletedAt')->comment('删除时间')->nullable();
            $table->integer('creatorId')->comment('创建人ID，重复字段，方便查询')->nullable();
            $table->integer('updaterId')->comment('最近更新人ID，重复字段，方便查询')->nullable();
            $table->integer('deleterId')->comment('删除人ID，重复字段，方便查询')->nullable();
            $table->string('createdBy', 1000)->comment('创建人')->nullable();
            $table->string('updatedBy', 1000)->comment('最近更新人')->nullable();
            $table->string('deletedBy', 1000)->comment('删除人')->nullable();
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('{{tableName}}');
    }
}
