<?php

use App\Enums\ContactMessages\RequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('request_status', 32)->default(RequestStatus::New->value)->after('read_at');
            $table->index('request_status');
        });

        $legacyMap = [
            'new' => RequestStatus::New->value,
            'pending' => RequestStatus::New->value,
            'unread' => RequestStatus::New->value,
            'open' => RequestStatus::New->value,
            'in_review' => RequestStatus::UnderReview->value,
            'review' => RequestStatus::UnderReview->value,
            'under_review' => RequestStatus::UnderReview->value,
            'contacted' => RequestStatus::Contacted->value,
            'quoted' => RequestStatus::QuoteSent->value,
            'quote_sent' => RequestStatus::QuoteSent->value,
            'agreed' => RequestStatus::Agreed->value,
            'accepted' => RequestStatus::Agreed->value,
            'rejected' => RequestStatus::Rejected->value,
            'declined' => RequestStatus::Rejected->value,
            'completed' => RequestStatus::Completed->value,
            'done' => RequestStatus::Completed->value,
            'closed' => RequestStatus::Completed->value,
        ];

        $allowed = RequestStatus::values();

        DB::table('contact_messages')
            ->select(['id', 'request_status'])
            ->orderBy('id')
            ->chunkById(100, function ($messages) use ($legacyMap, $allowed) {
                foreach ($messages as $message) {
                    $raw = strtolower(trim((string) $message->request_status));
                    $mapped = in_array($raw, $allowed, true)
                        ? $raw
                        : ($legacyMap[$raw] ?? RequestStatus::New->value);

                    if ($mapped !== $message->request_status) {
                        DB::table('contact_messages')
                            ->where('id', $message->id)
                            ->update(['request_status' => $mapped]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['request_status']);
            $table->dropColumn('request_status');
        });
    }
};
