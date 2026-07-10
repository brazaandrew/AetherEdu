<?php
declare(strict_types=1);

/**
 * Authentication Configuration
 */

return [
    // Session configuration
    'session' => [
        'name' => 'eLMS_session',
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    
    // Password policy
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true
    ],
    
    // Roles configuration
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => [
                'manage_users',
                'manage_subjects',
                'view_all_grades',
                'generate_reports'
            ]
        ],
        'teacher' => [
            'name' => 'Teacher',
            'permissions' => [
                'create_activities',
                'create_quizzes',
                'grade_submissions',
                'view_class_grades'
            ]
        ],
        'student' => [
            'name' => 'Student',
            'permissions' => [
                'submit_activities',
                'take_quizzes',
                'view_own_grades'
            ]
        ]
    ],
    
    // Login security
    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'enable_2fa' => false
    ],
    
    // Redirect URLs
    'redirects' => [
        'after_login' => '/dashboard.php',
        'after_logout' => '/login.php',
        'guest_access' => '/login.php'
    ]
];