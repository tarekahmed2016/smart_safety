<?php

use App\Support\HomepageSectionTitleSource;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomepageSectionTitleSource::copyLegacyCompanyFieldsIntoSectionSettings();
    }

    public function down(): void
    {
        // Legacy company_info columns are kept; copied Homepage Section settings are retained.
    }
};
