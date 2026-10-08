<?php
// ============================================================
// Staj Takip - Kimlik doğrulama (session tabanlı)
// ============================================================
declare(strict_types=1);

namespace App;

class Auth
{
    const SESSION_KEY = 'user';

    /**
     * Kullanıcıyı oturum açtırır.
     * @return array|null Oturum açan kullanıcı (role dahil)
     */
    public static function login(string $email, string $password, ?string $expectedRole = null): ?array
    {
        $db = Database::get();
        $sql = 'SELECT * FROM users WHERE email = ?';
        $params = [$email];
        if ($expectedRole !== null) {
            $sql .= ' AND role = ?';
            $params[] = $expectedRole;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $user = $stmt->fetch();

        if (!$user) return null;

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);
        $_SESSION[self::SESSION_KEY] = $user;
        // Oturum kimliğini yenile (session fixation koruması).
        session_regenerate_id(true);
        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        $u = $_SESSION[self::SESSION_KEY] ?? null;
        return $u ?: null;
    }

    public static function id(): ?int
    {
        return $_SESSION[self::SESSION_KEY]['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION[self::SESSION_KEY]['role'] ?? null;
    }

    public static function isTeacher(): bool { return self::role() === 'teacher'; }
    public static function isStudent(): bool { return self::role() === 'student'; }
    public static function check(): bool     { return isset($_SESSION[self::SESSION_KEY]); }

    /**
     * Öğrenci kaydı. Veli onayı zorunlu kuralı içerir.
     * @return array {ok:bool, error?:string, user?:array}
     */
    public static function register(array $data): array
    {
        $db = Database::get();

        // Temel validasyon
                $name      = trim($data['name'] ?? '');
        $email     = trim($data['email'] ?? '');
        $password  = $data['password'] ?? '';
        $isMinor   = !empty($data['is_minor']) ? 1 : 0; // checkbox işaretlenirse 18 yaş altı varsay.

        if ($name === '' || $email === '' || $password === '') {
            return ['ok' => false, 'error' => 'Tüm alanları doldurunuz.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Geçerli bir e-posta giriniz.'];
        }
        if (strlen($password) < 6) {
            return ['ok' => false, 'error' => 'Şifre en az 6 karakter olmalıdır.'];
        }
        // KVKK onayı zorunlu
        if (empty($data['consent_kvkk'])) {
            return ['ok' => false, 'error' => 'KVKK aydınlatma metnini kabul etmelisiniz.'];
        }
        // Reşit ya da veli onayı
        if ($isMinor && empty($data['consent_parent'])) {
            return ['ok' => false, 'error' => '18 yaş altındaysanız veli onayı gerekmektedir.'];
        }

        // E-posta tekrarlı mı?
        $exists = $db->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            return ['ok' => false, 'error' => 'Bu e-posta zaten kayıtlı. Giriş yapın.', 'email_exists' => true];
        }

                $hash = password_hash($password, Config::get('auth.hash_algo'));
        $stmt = $db->prepare(
            'INSERT INTO users (email,name,role,password_hash,is_minor,parent_consent_given,parent_consent_at) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([$email, $name, 'student', $hash, $isMinor, $isMinor ? 1 : 0, $isMinor ? date('Y-m-d H:i:s') : null]);
        $userId = (int)$db->lastInsertId();

        // Consent kayıtları.
        $db->prepare('INSERT INTO consents (user_id,consent_type,given,given_at) VALUES (?,?,?,?)')
           ->execute([$userId, 'kvkk', 1, date('Y-m-d H:i:s')]);
        if ($isMinor) {
            $db->prepare('INSERT INTO consents (user_id,consent_type,given,given_at) VALUES (?,?,?,?)')
               ->execute([$userId, 'parent', 1, date('Y-m-d H:i:s')]);
        }

        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        unset($user['password_hash']);

        // Oturum aç.
        $_SESSION[self::SESSION_KEY] = $user;

        return ['ok' => true, 'user' => $user];
    }
}
