<?php

/**
 * Checks if the current session belongs to an authorized admin.
 * Roles: 2 = admin, 3 = superAdmin
 */
function is_admin(): bool {
    return isset($_SESSION['rbac_role']) && (int)$_SESSION['rbac_role'] >= 2;
}

/**
 * Checks if the current session belongs to a superAdmin.
 * Role: 3 = superAdmin
 */
function is_super_admin(): bool {
    return isset($_SESSION['rbac_role']) && (int)$_SESSION['rbac_role'] === 3;
}
