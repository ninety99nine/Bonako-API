<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Pivots\UserOrderCollectionAssociation;
use Illuminate\Database\Migrations\Migration;

class CreateUserOrderCollectionAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_order_collection_association', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->enum('role', UserOrderCollectionAssociation::ROLES)->default(Arr::last(UserOrderCollectionAssociation::ROLES));
            $table->boolean('can_collect')->default(false);
            $table->char('collection_code', 6)->nullable();
            $table->string('collection_qr_code')->nullable();
            $table->timestamp('collection_code_expires_at')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('role');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('collection_code');

            /*  Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_order_collection_association');
    }
}
