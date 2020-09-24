<?php
/**
  * SystemDefinitionsInit loads the list of Survloop system variables and their defaults.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers;

class SystemDefinitionsInit
{   
    protected function getDefaultStyles()
    {
        return [
            'font-main'         => [
                'Helvetica,Arial,sans-serif', 
                'Universal Font Family'
            ],
            'color-main-bg'     => ['#FFF',    'Background Color'],
            'color-main-text'   => ['#333',    'Text Color'],
            'color-main-link'   => ['#416CBD', 'Link Color'],
            'color-main-grey'   => ['#999',    'Grey Color'],
            'color-main-faint'  => ['#EDF8FF', 'Faint Color'],
            'color-main-faintr' => ['#F9FCFF', 'Fainter Color'],
            'color-main-on'     => ['#2B3493', 'Primary Color'],
            'color-info-on'     => ['#5BC0DE', 'Info Color'],
            'color-danger-on'   => ['#EC2327', 'Danger Color'],
            'color-success-on'  => ['#006D36', 'Success Color'],
            'color-warn-on'     => ['#F0AD4E', 'Warning Color'],
            'color-line-hr'     => ['#999',    'Horizontal Rule Color'],
            'color-field-bg'    => ['#FFF',    'Form Field BG Color'],
            'color-form-text'   => ['#333',    'Form Field Text Color'],
            'color-nav-bg'      => ['#000',    'Navigation BG Color'],
            'color-nav-text'    => ['#888',    'Navigation Text Color']
        ];
    }
    
    protected function getDefaultSys()
    {
        return [
            'site-name' => [
                'Installation/Site Name', 
                'for general reference, in English'
            ], 
            'cust-abbr' => [
                'Installation Abbreviation',
                'Survloop'
            ], 
            'cust-vend' => [
                'Installation Vendor',
                'RockHopSoft'
            ], 
            'cust-package' => [
                'Vendor Package Name', 
                'rockhopsoft/survloop'
            ], 
            // for files and folder names, no spaces or special characters
            'app-url' => [
                'Primary Application URL',
                'http://myapp.com'
            ], 
            'logo-url' => [
                'URL Linked To Logo',
                '/optionally-different'
            ], 
            'app-root-path' => [
                'Absolute Path To App Root',
                '/var/www/laravel'
            ], 
            'meta-title' => [
                'SEO Default Meta Title', 
                ''
            ], 
            'meta-desc' => [
                'SEO Default Meta Description', 
                ''
            ], 
            'meta-keywords' => [
                'SEO Default Meta Keywords', 
                ''
            ], 
            'meta-img' => [
                'SEO Default Meta Social Media Sharing Image', 
                ''
            ], 
            'logo-img-lrg' => [
                'Large Logo Image', 
                '/siteabrv/uploads/logo-large.png'
            ], 
            'logo-img-md' => [
                'Medium Logo Image', 
                '/siteabrv/uploads/logo-medium.png'
            ], 
            'logo-img-sm' => [
                'Small Logo Image', 
                '/siteabrv/uploads/logo-small.png'
            ], 
            'shortcut-icon' => [
                'Shortcut Icon Image', 
                '/siteabrv/ico.png'
            ],
            'spinner-code' => [
                'Spinner Animation', 
                '&lt;i class="fa-li fa fa-spinner fa-spin"&gt;&lt;/i&gt;'
            ], 
            'matomo-analytic-url' => [
                'Matomo Cloud Analytics URL', 
                'myapp.matomo.cloud'
            ], 
            'matomo-analytic-site-id' => [
                'Matomo Cloud Analytics Site ID', 
                '1'
            ], 
            'google-analytic' => [
                'Google Analytics Tracking ID', 
                'UA-23427655-1'
            ], 
            'google-map-key' => [
                'Google Maps API Key: Server', 
                'string'
            ], 
            'google-map-key2' => [
                'Google Maps API Key: Browser', 
                'string'
            ], 
            'google-cod-key' => [
                'Google Geocoding API Key: Server', 
                'string'
            ], 
            'google-cod-key2' => [
                'Google Geocoding API Key: Browser', 
                'string'
            ], 
            'twitter' => [
                'Twitter Account', 
                '@Survloop'
            ], 
            'facebook-app-id' => [
                'Facebook App ID', 
                '234775309892416'
            ], 
            'show-logo-title' => [
                'Print Site Name Next To Logo', 
                '1 or 0'
            ], 
            'users-create-db' => [
                'Users Can Create Databases', 
                '1 or 0'
            ], 
            'user-name-req' => [
                'Username Are Required To Register', 
                '1 or 0'
            ], 
            'has-partners' => [
                'Has Partners User Area', 
                '1 or 0'
            ], 
            'has-volunteers' => [
                'Has Volunteer User Area', 
                '1 or 0'
            ], 
            'has-avatars' => [
                'Default User Avatar Image', 
                '/siteabrv/uploads/avatar-shell.png'
            ], 
            'has-canada' => [
                'Has Canadian Maps', 
                '1 or 0'
            ], 
            'parent-company' => [
                'Parent Company of This Installation', 
                'MegaOrg'
            ], 
            'parent-website' => [
                'Parent Company\'s Website URL', 
                'http://www...'
            ], 
            'login-instruct' => [
                'User Login Instructions', 
                'HTML'
            ], 
            'signup-instruct' => [
                'New User Sign Up Instructions', 
                'HTML'
            ], 
            'app-license' => [
                'License Info', 
                'Creative Commons Attribution-ShareAlike License'
            ], 
            'app-license-url' => [
                'License Info URL', 
                'http://creativecommons.org/licenses/by-sa/3.0/'
            ], 
            'app-license-img' => [
                'License Info Image', 
                '/survloop/uploads/creative-commons-by-sa-88x31.png'
            ],
            'app-license-snc' => [
                'License Since Year', 
                date("Y")
            ],
            'css-extra-files' => [
                'CSS Extra Files', 
                'comma separated'
            ],
            'header-code' => [
                '< head > Header Code < / head >', 
                '&lt;div&gt;Anything&lt;/div&gt;'
            ],
            'sys-cust-js' => [
                'System-Wide Javascript', 
                'var custom = 1;'
            ],
            'sys-cust-ajax' => [
                'System-Wide jQuery/AJAX', 
                'function reqFormFldCustom() { return 0; }'
            ]
        ];
    }
}