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
            'color-nav-text'    => ['#888',    'Navigation Text Color'],
            'color-nav-logo'    => ['#FFF',    'Navigation Logo Color']
        ];
    }
    
    protected function getDefaultSys()
    {
        return [
            'site-name' => [
                'Installation/Site Name', 
                'My Survloop Project'
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
                '/home'
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
                ''
            ], 
            'logo-img-md' => [
                'Medium Logo Image', 
                ''
            ], 
            'logo-img-sm' => [
                'Small Logo Image', 
                ''
            ], 
            'shortcut-icon' => [
                'Shortcut Icon Image', 
                '/survloop/uploads/survloop-ico.png'
            ],
            'spinner-code' => [
                'Spinner Animation', 
                '<i class="fa-li fa fa-spinner fa-spin"></i>'
            ], 
            'matomo-analytic-url' => [
                'Matomo Cloud Analytics URL', 
                ''
            ], 
            'matomo-analytic-site-id' => [
                'Matomo Cloud Analytics Site ID', 
                ''
            ], 
            'google-analytic' => [
                'Google Analytics Tracking ID', 
                ''
            ], 
            'google-map-key' => [
                'Google Maps API Key: Server', 
                ''
            ], 
            'google-map-key2' => [
                'Google Maps API Key: Browser', 
                ''
            ], 
            'google-cod-key' => [
                'Google Geocoding API Key: Server', 
                ''
            ], 
            'google-cod-key2' => [
                'Google Geocoding API Key: Browser', 
                ''
            ], 
            'twitter' => [
                'Twitter Account', 
                ''
            ], 
            'facebook-app-id' => [
                'Facebook App ID', 
                ''
            ], 
            'show-logo-title' => [
                'Print Site Name Next To Logo', 
                '0'
            ], 
            'users-create-db' => [
                'Users Can Create Databases', 
                '0'
            ], 
            'user-name-req' => [
                'Username Are Required To Register', 
                '0'
            ], 
            'has-partners' => [
                'Has Partners User Area', 
                '0'
            ], 
            'has-volunteers' => [
                'Has Volunteer User Area', 
                '0'
            ], 
            'has-avatars' => [
                'Default User Avatar Image', 
                '/survloop/uploads/avatar-shell.png'
            ], 
            'has-canada' => [
                'Has Canadian Maps', 
                '1'
            ], 
            'parent-company' => [
                'Parent Company of This Installation', 
                ''
            ], 
            'parent-website' => [
                'Parent Company\'s Website URL', 
                ''
            ], 
            'login-instruct' => [
                'User Login Instructions', 
                ''
            ], 
            'signup-instruct' => [
                'New User Sign Up Instructions', 
                ''
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
                ''
            ],
            'header-code' => [
                '< head > Header Code < / head >', 
                '<!-- Anything -->'
            ],
            'sys-cust-js' => [
                'System-Wide Javascript', 
                'var myProjectCustomJava = 1;'
            ],
            'sys-cust-ajax' => [
                'System-Wide jQuery/AJAX', 
                'function reqFormFldCustomMyProject() { return 0; }'
            ]
        ];
    }
}