<?php

use App\Models\Store;
use Illuminate\Support\Arr;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();

            /*  Basic Information  */
            $table->enum('payment_status', Transaction::STATUSES)->default(Arr::last(Transaction::STATUSES));
            $table->string('description')->nullable();
            $table->string('proof_of_payment_photo')->nullable();

            /*  Amount Information  */
            $table->char('currency', 3)->default(Store::CURRENCY);
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedTinyInteger('percentage')->default(100);
            $table->foreignId('payment_method_id')->constrained();

            /*  DPO Information  */
            $table->string('dpo_payment_url')->nullable();
            $table->timestamp('dpo_payment_url_expires_at')->nullable();
            $table->json('dpo_payment_response')->nullable();

            /*  Orange Money Information  */
            $table->json('orange_money_payment_response')->nullable();

            /*  Payer Information  */
            $table->foreignId('paid_by_user_id')->constrained()->nullable();

            /*  Verifier Information  */
            $table->boolean('is_verified')->default(false);
            $table->enum('verified_by', Transaction::VERIFIERS);
            $table->foreignId('verified_by_user_id')->constrained()->nullable();

            /*  Requester Information  */
            $table->foreignId('requested_by_user_id')->constrained()->nullable();

            /*  Cancellation Information  */
            $table->boolean('is_cancelled')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by_user_id')->constrained()->nullable();

            /*  Owenership Information  */
            $table->string('owner_type');
            $table->foreignId('owner_id');

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('payment_status');
            $table->index('paid_by_user_id');
            $table->index('verified_by_user_id');
            $table->index('requested_by_user_id');
            $table->index('cancelled_by_user_id');

            /* Foreign Key Constraints */
            $table->foreign('paid_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('verified_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('requested_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancelled_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
