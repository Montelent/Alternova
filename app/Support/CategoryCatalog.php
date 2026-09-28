<?php

namespace App\Support;

class CategoryCatalog
{
    /**
     * Canonical category names used across admin + public finder.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        return [
            'Analytics',
            'Chat & Communication',
            'CRM',
            'Design',
            'Dev Tools',
            'Docs & Knowledge',
            'E-commerce',
            'Email',
            'Finance',
            'Forms & Surveys',
            'Hosting & Infra',
            'Media & Images',
            'Monitoring',
            'Notes & Productivity',
            'Project Management',
            'Security',
            'Storage & Files',
            'Video & Meetings',
        ];
    }
}
