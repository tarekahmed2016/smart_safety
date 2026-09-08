<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->string('hero_highlight_ar')->nullable()->after('hero_description_en');
            $table->string('hero_highlight_en')->nullable()->after('hero_highlight_ar');
            $table->string('hero_primary_cta_text_ar')->nullable()->after('hero_highlight_en');
            $table->string('hero_primary_cta_text_en')->nullable()->after('hero_primary_cta_text_ar');
            $table->string('hero_primary_cta_url')->nullable()->after('hero_primary_cta_text_en');
            $table->string('hero_secondary_cta_text_ar')->nullable()->after('hero_primary_cta_url');
            $table->string('hero_secondary_cta_text_en')->nullable()->after('hero_secondary_cta_text_ar');
            $table->string('hero_secondary_cta_url')->nullable()->after('hero_secondary_cta_text_en');
            $table->string('products_section_title_ar')->nullable()->after('hero_secondary_cta_url');
            $table->string('products_section_title_en')->nullable()->after('products_section_title_ar');
            $table->unsignedSmallInteger('products_homepage_limit')->default(8)->after('products_section_title_en');
            $table->string('industries_section_title_ar')->nullable()->after('products_homepage_limit');
            $table->string('industries_section_title_en')->nullable()->after('industries_section_title_ar');
            $table->string('about_section_title_ar')->nullable()->after('industries_section_title_en');
            $table->string('about_section_title_en')->nullable()->after('about_section_title_ar');
            $table->string('about_cta_text_ar')->nullable()->after('about_section_title_en');
            $table->string('about_cta_text_en')->nullable()->after('about_cta_text_ar');
            $table->string('about_cta_url')->nullable()->after('about_cta_text_en');
            $table->string('gallery_section_title_ar')->nullable()->after('about_cta_url');
            $table->string('gallery_section_title_en')->nullable()->after('gallery_section_title_ar');
            $table->string('contact_section_title_ar')->nullable()->after('gallery_section_title_en');
            $table->string('contact_section_title_en')->nullable()->after('contact_section_title_ar');
            $table->string('contact_section_subtitle_ar')->nullable()->after('contact_section_title_en');
            $table->string('contact_section_subtitle_en')->nullable()->after('contact_section_subtitle_ar');
            $table->text('footer_description_ar')->nullable()->after('contact_section_subtitle_en');
            $table->text('footer_description_en')->nullable()->after('footer_description_ar');
            $table->string('footer_newsletter_title_ar')->nullable()->after('footer_description_en');
            $table->string('footer_newsletter_title_en')->nullable()->after('footer_newsletter_title_ar');
            $table->text('footer_newsletter_description_ar')->nullable()->after('footer_newsletter_title_en');
            $table->text('footer_newsletter_description_en')->nullable()->after('footer_newsletter_description_ar');
            $table->string('footer_newsletter_button_ar')->nullable()->after('footer_newsletter_description_en');
            $table->string('footer_newsletter_button_en')->nullable()->after('footer_newsletter_button_ar');
            $table->string('footer_newsletter_placeholder_ar')->nullable()->after('footer_newsletter_button_en');
            $table->string('footer_newsletter_placeholder_en')->nullable()->after('footer_newsletter_placeholder_ar');
            $table->string('footer_copyright_ar')->nullable()->after('footer_newsletter_placeholder_en');
            $table->string('footer_copyright_en')->nullable()->after('footer_copyright_ar');
        });
    }

    public function down(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->dropColumn([
                'hero_highlight_ar',
                'hero_highlight_en',
                'hero_primary_cta_text_ar',
                'hero_primary_cta_text_en',
                'hero_primary_cta_url',
                'hero_secondary_cta_text_ar',
                'hero_secondary_cta_text_en',
                'hero_secondary_cta_url',
                'products_section_title_ar',
                'products_section_title_en',
                'products_homepage_limit',
                'industries_section_title_ar',
                'industries_section_title_en',
                'about_section_title_ar',
                'about_section_title_en',
                'about_cta_text_ar',
                'about_cta_text_en',
                'about_cta_url',
                'gallery_section_title_ar',
                'gallery_section_title_en',
                'contact_section_title_ar',
                'contact_section_title_en',
                'contact_section_subtitle_ar',
                'contact_section_subtitle_en',
                'footer_description_ar',
                'footer_description_en',
                'footer_newsletter_title_ar',
                'footer_newsletter_title_en',
                'footer_newsletter_description_ar',
                'footer_newsletter_description_en',
                'footer_newsletter_button_ar',
                'footer_newsletter_button_en',
                'footer_newsletter_placeholder_ar',
                'footer_newsletter_placeholder_en',
                'footer_copyright_ar',
                'footer_copyright_en',
            ]);
        });
    }
};
