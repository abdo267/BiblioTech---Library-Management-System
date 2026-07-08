<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

// Session Helper
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['created_time'])) {
            $_SESSION['created_time'] = time();
        } elseif (time() - $_SESSION['created_time'] > SESSION_LIFETIME) {
            self::destroy();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        }
    }

    public static function setFlash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function hasFlash($type) {
        return isset($_SESSION['flash'][$type]);
    }
}

// Auth Helper
class Auth {
    public static function login($user) {
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('logged_in', true);
        
        if ($user['role'] === 'member') {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, full_name, status FROM members WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $member = $stmt->fetch();
                if ($member) {
                    Session::set('member_id', $member['id']);
                    Session::set('member_name', $member['full_name']);
                    Session::set('member_status', $member['status']);
                }
            } catch (Exception $e) {
                // Fail-safe
            }
        }
    }

    public static function logout() {
        Session::destroy();
    }

    public static function isLoggedIn() {
        return Session::get('logged_in', false) === true;
    }

    public static function getUserId() {
        return Session::get('user_id');
    }

    public static function getMemberId() {
        return Session::get('member_id');
    }

    public static function getUserEmail() {
        return Session::get('user_email');
    }

    public static function getUserRole() {
        return Session::get('user_role');
    }

    public static function getMemberName() {
        return Session::get('member_name', 'Member');
    }

    public static function isAdmin() {
        return self::isLoggedIn() && self::getUserRole() === 'admin';
    }

    public static function isMember() {
        return self::isLoggedIn() && self::getUserRole() === 'member';
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to access this page.');
            header('Location: ' . BASE_URL . '/index.php?route=login');
            exit;
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            Session::setFlash('error', 'Unauthorized access! Admin privileges required.');
            header('Location: ' . BASE_URL . '/index.php?route=home');
            exit;
        }
    }

    public static function requireMember() {
        self::requireLogin();
        if (!self::isMember()) {
            Session::setFlash('error', 'Unauthorized access! Member privileges required.');
            header('Location: ' . BASE_URL . '/index.php?route=home');
            exit;
        }
        
        if (Session::get('member_status') !== 'active') {
            Session::setFlash('error', 'Your member account is suspended. Contact the administrator.');
            self::logout();
            header('Location: ' . BASE_URL . '/index.php?route=login');
            exit;
        }
    }
}

// Validator Helper
class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';

            foreach ($fieldRules as $rule => $ruleValue) {
                if ($rule === 'required' && ($value === '' || $value === null)) {
                    $fieldName = ucfirst(str_replace('_', ' ', $field));
                    $this->errors[$field] = "{$fieldName} is required.";
                    break;
                }

                if ($value === '' || $value === null) {
                    continue;
                }

                switch ($rule) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field] = "Please enter a valid email address.";
                        }
                        break;
                    case 'min':
                        if (strlen($value) < $ruleValue) {
                            $fieldName = ucfirst(str_replace('_', ' ', $field));
                            $this->errors[$field] = "{$fieldName} must be at least {$ruleValue} characters.";
                        }
                        break;
                    case 'max':
                        if (strlen($value) > $ruleValue) {
                            $fieldName = ucfirst(str_replace('_', ' ', $field));
                            $this->errors[$field] = "{$fieldName} must not exceed {$ruleValue} characters.";
                        }
                        break;
                    case 'matches':
                        $compareValue = isset($data[$ruleValue]) ? trim($data[$ruleValue]) : '';
                        if ($value !== $compareValue) {
                            $fieldName = ucfirst(str_replace('_', ' ', $field));
                            $compareName = ucfirst(str_replace('_', ' ', $ruleValue));
                            $this->errors[$field] = "{$fieldName} must match {$compareName}.";
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($value)) {
                            $fieldName = ucfirst(str_replace('_', ' ', $field));
                            $this->errors[$field] = "{$fieldName} must be a number.";
                        }
                        break;
                    case 'year':
                        if (!is_numeric($value) || intval($value) < 1000 || intval($value) > intval(date('Y')) + 1) {
                            $this->errors[$field] = "Please enter a valid publication year.";
                        }
                        break;
                    case 'isbn':
                        $cleanIsbn = str_replace(['-', ' '], '', $value);
                        if (strlen($cleanIsbn) !== 10 && strlen($cleanIsbn) !== 13) {
                            $this->errors[$field] = "ISBN must be a valid 10 or 13 digit code.";
                        }
                        break;
                }
            }
        }
        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return reset($this->errors);
    }
}


// 4. File Uploader Helper

class Uploader {
    public static function uploadCover($file, $targetDir = UPLOAD_PATH) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['status' => true, 'filename' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'error' => 'An error occurred during file upload. Code: ' . $file['error']];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['status' => false, 'error' => 'File size exceeds maximum limit of 2MB.'];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['status' => false, 'error' => 'Invalid file format. Only JPG, PNG, and WEBP images are allowed.'];
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = ($mimeType === 'image/png') ? 'png' : (($mimeType === 'image/webp') ? 'webp' : 'jpg');
        }
        $fileName = uniqid('cover_', true) . '.' . $extension;
        $targetFile = $targetDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['status' => true, 'filename' => $fileName];
        }
        return ['status' => false, 'error' => 'Failed to write file to disk. Check folder write permissions.'];
    }

    public static function downloadCoverFromUrl($url, $targetDir = UPLOAD_PATH) {
        $url = trim($url);
        if ($url === '') {
            return ['status' => true, 'filename' => null];
        }

        $url = str_replace('http://', 'https://', $url);

        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'BiblioTech/1.0'
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true
            ]
        ]);

        $imageData = @file_get_contents($url, false, $context);
        if ($imageData === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_USERAGENT => 'BiblioTech/1.0',
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode !== 200 || $imageData === false) {
                return ['status' => false, 'error' => 'Could not download cover image.'];
            }
        } elseif ($imageData === false) {
            return ['status' => false, 'error' => 'Could not download cover image.'];
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($fileInfo, $imageData);
        finfo_close($fileInfo);

        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($allowedTypes[$mimeType])) {
            return ['status' => false, 'error' => 'Downloaded cover is not a supported image format.'];
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = uniqid('cover_', true) . '.' . $allowedTypes[$mimeType];
        $targetFile = $targetDir . '/' . $fileName;

        if (file_put_contents($targetFile, $imageData) !== false) {
            return ['status' => true, 'filename' => $fileName];
        }
        return ['status' => false, 'error' => 'Failed to save cover image to disk.'];
    }
}

// Email Simulation Helper
class EmailSim {
    public static function send($to, $subject, $messageBody) {
        $timestamp = date('Y-m-d H:i:s');
        $logContent = "========================================================\n";
        $logContent .= "TIMESTAMP: {$timestamp}\n";
        $logContent .= "TO: {$to}\n";
        $logContent .= "SUBJECT: {$subject}\n";
        $logContent .= "MESSAGE:\n{$messageBody}\n";
        $logContent .= "========================================================\n\n";

        try {
            file_put_contents(EMAIL_LOG_PATH, $logContent, FILE_APPEND);
        } catch (Exception $e) {
           
        }

        Session::setFlash('email_sim', "Simulation Email sent to <strong>" . htmlspecialchars($to) . "</strong>. Logged to <code>email_simulation.log</code>");
        return true;
    }
}

// Stripe Payment Helper
class StripeService {
    private static function isConfigured() {
        return STRIPE_SECRET_KEY !== '' && STRIPE_PUBLISHABLE_KEY !== '';
    }

    private static function request($method, $endpoint, $params = []) {
        if (!self::isConfigured()) {
            throw new Exception('Stripe is not configured. Add your API keys in config/config.local.php');
        }

        $url = 'https://api.stripe.com/v1/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode >= 400 || isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Stripe request failed.';
            throw new Exception($message);
        }

        return $data;
    }

    public static function createPaymentIntent($amountCents, $metadata = []) {
        $params = [
            'amount' => intval($amountCents),
            'currency' => PAYMENT_CURRENCY,
            'payment_method_types[0]' => 'card',
        ];

        foreach ($metadata as $key => $value) {
            $params['metadata[' . $key . ']'] = $value;
        }

        return self::request('POST', 'payment_intents', $params);
    }

    public static function retrievePaymentIntent($paymentIntentId) {
        return self::request('GET', 'payment_intents/' . urlencode($paymentIntentId));
    }

    public static function verifyWebhookSignature($payload, $sigHeader) {
        if (STRIPE_WEBHOOK_SECRET === '') {
            return json_decode($payload, true);
        }

        $parts = [];
        foreach (explode(',', $sigHeader) as $item) {
            $pair = explode('=', trim($item), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        if (empty($parts['t']) || empty($parts['v1'])) {
            throw new Exception('Invalid Stripe signature header.');
        }

        $signedPayload = $parts['t'] . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, STRIPE_WEBHOOK_SECRET);

        if (!hash_equals($expected, $parts['v1'])) {
            throw new Exception('Stripe webhook signature verification failed.');
        }

        return json_decode($payload, true);
    }
}
