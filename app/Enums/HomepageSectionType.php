<?php

namespace App\Enums;

enum HomepageSectionType: string
{
    case Hero = 'hero';
    case Features = 'features';
    case Products = 'products';
    case Services = 'services';
    case CustomManufacturing = 'custom_manufacturing';
    case Industries = 'industries';
    case About = 'about';
    case Gallery = 'gallery';
    case ContactCta = 'contact_cta';
    case ContactForm = 'contact_form';
}
