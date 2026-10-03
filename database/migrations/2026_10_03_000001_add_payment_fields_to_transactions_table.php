<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('method')->default('cash')->after('total_price');
            $table->string('status')->default('paid')->after('method');
            $table->string('external_id')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('external_id');
            $table->timestamp('expires_at')->nullable()->after('paid_at');
            $table->index(['user_id', 'status']);
            $table->index('external_id');
        });

        DB::table('transactions')->orderBy('id')->chunkById(200, function ($transactions) {
            foreach ($transactions as $transaction) {
                $detail = DB::table('transaction_details')
                    ->where('transaction_id', $transaction->id)
                    ->orderByDesc('id')
                    ->first();

                if (! $detail) {
                    continue;
                }

                DB::table('transactions')->where('id', $transaction->id)->update([
                    'method' => $detail->method ?? 'cash',
                    'status' => $detail->status ?? 'paid',
                    'external_id' => $detail->external_id,
                    'paid_at' => $detail->paid_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['external_id']);
            $table->dropColumn(['method', 'status', 'external_id', 'paid_at', 'expires_at']);
        });
    }
};
