<?php

use App\Models\CompanyInfo;
use App\Support\HomepageContactContentDefaults;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CompanyInfo::query()->update(HomepageContactContentDefaults::companyFields());
    }

    public function down(): void
    {
        CompanyInfo::query()->update([
            'contact_section_title_ar' => 'تواصل معنا',
            'contact_section_title_en' => 'Contact Us',
            'contact_section_subtitle_ar' => 'تواصل معنا باستخدام البيانات أدناه أو أرسل لنا رسالة.',
            'contact_section_subtitle_en' => 'Reach out to us using the details below or send us a message.',
        ]);
    }
};
