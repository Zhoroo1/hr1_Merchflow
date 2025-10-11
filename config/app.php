<?php
/* Global toggles & mail/dev config */

/** Turn 2FA on/off in one place */
define('USE_2FA', true);

/** OTP validity (seconds) */
define('OTP_TTL', 300); // 5 minutes

/** Resend rate-limit (seconds) */
define('OTP_RESEND_COOLDOWN', 60);

/**
 * Dev mail redirect:
 *   - Kapag may value (valid email), LAHAT ng OTP ay papadala sa email na ito
 *     (useful sa local dev kapag dummy ang user email tulad ng admin@osave.com).
 *   - Gawin '' (empty string) para i-send sa totoong user email.
 */
define('DEV_MAIL_REDIRECT', 'danv66215@gmail.com'); // set '' to disable
