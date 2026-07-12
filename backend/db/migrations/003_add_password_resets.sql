-- 003: Token-based password reset (replaces the client-only ForgotPassword stub).
-- `token` stores the SHA-256 hex of the emailed token (never the raw token).

CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  userId INT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  expiresAt DATETIME NOT NULL,
  usedAt DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX password_resets_token_idx (token),
  CONSTRAINT password_resets_user_fk FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);
