<?php

use App\Support\HomepageSectionTitleSource;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomepageSectionTitleSource::copyLegacyPromoTitlesIntoHomepageSections();
    }

    public function down(): void
    {
        // Legacy company_info title columns are kept; copied Homepage Section titles are retained.
    }
};
