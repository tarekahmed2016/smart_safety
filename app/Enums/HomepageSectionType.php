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
    case TeamMembers = 'team_members';
    case ClientsPartners = 'clients_partners';
    case Gallery = 'gallery';
    case ContactCta = 'contact_cta';
    case ContactForm = 'contact_form';
}
