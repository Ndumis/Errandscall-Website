<?php
// Limits repeated failed attempts (login, password-reset code, reset emails) to stop
// automated password and code guessing. Attempts are stored in the login_attempts
// table (created in config/database.php) and counted per account and per IP address.

if (!function_exists('tooManyAttempts')) {
    /**
     * True when the account ($identifier) or the IP address has reached its limit
     * within the last $window_minutes.
     */
    function tooManyAttempts($conn, $type, $identifier, $max_per_identifier, $max_per_ip, $window_minutes) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $identifier = strtolower(trim($identifier));

        $stmt = $conn->prepare("SELECT
                COALESCE(SUM(identifier = ?), 0) AS by_identifier,
                COALESCE(SUM(ip_address = ?), 0) AS by_ip
            FROM login_attempts
            WHERE attempt_type = ?
              AND created_at > (NOW() - INTERVAL ? MINUTE)
              AND (identifier = ? OR ip_address = ?)");
        $stmt->bind_param("sssiss", $identifier, $ip, $type, $window_minutes, $identifier, $ip);
        $stmt->execute();
        $counts = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) $counts['by_identifier'] >= $max_per_identifier
            || (int) $counts['by_ip'] >= $max_per_ip;
    }

    /** Record one attempt, and tidy up attempts older than a day. */
    function recordAttempt($conn, $type, $identifier) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $identifier = strtolower(trim($identifier));

        $stmt = $conn->prepare("INSERT INTO login_attempts (attempt_type, identifier, ip_address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $type, $identifier, $ip);
        $stmt->execute();
        $stmt->close();

        $conn->query("DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)");
    }

    /** Forget an account's attempts after it succeeds. */
    function clearAttempts($conn, $type, $identifier) {
        $identifier = strtolower(trim($identifier));
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE attempt_type = ? AND identifier = ?");
        $stmt->bind_param("ss", $type, $identifier);
        $stmt->execute();
        $stmt->close();
    }
}
