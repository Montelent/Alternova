# Production deployment security checklist

## Nginx upload execution protection

The repository includes Apache .htaccess protections, but Nginx does not read those files. Before exposing user uploads on an Nginx host:

1. Copy nginx-upload-security.conf to the server (for example, /etc/nginx/snippets/alternova-upload-security.conf).
2. Include it inside the application's HTTPS server block.
3. Keep the snippet's executable-extension deny rules ahead of the generic PHP/FastCGI handler.
4. Run nginx -t and reload Nginx.
5. Verify that a test file ending in .php under both /uploads/ and /storage/ returns 404 and is never executed. Verify normal JPEG, PNG, WebP, and GIF images still load.

Do not place the snippet inside a location ^~ /uploads/ or location ^~ /storage/ block: that can bypass the regular-expression deny rules.

## Application environment

- Set APP_ENV=production, APP_DEBUG=false, and a unique, high-entropy APP_KEY.
- Set APP_URL to the canonical deployment origin. Add every additional accepted hostname to APP_ALLOWED_HOSTS as a comma-separated list; do not use wildcards.
- If behind a reverse proxy, set TRUSTED_PROXIES to the exact proxy IP addresses or CIDRs. Never use *.
- Keep .env, Composer files, source maps, logs, backups, and database exports outside the public document root.
- Run migrations as part of deployment: php artisan migrate --force. Do not manually delete the installer lock or bypass the one-time reopen flow.
- Ensure storage/ and bootstrap/cache/ are writable by the PHP worker but not world-writable.
- Restrict outbound network access at the host/container firewall where practical. Application SSRF validation is an additional control, not a replacement for egress policy.
- Keep HTTPS enabled and use HSTS only after all accepted domains are served over HTTPS.

## Verification

After deployment, verify:

- /robots.txt, /sitemap.xml, and each child sitemap use the currently requested allow-listed domain, while an unrecognized Host header never controls canonical output.
- The login, registration, account, admin, installer, support-ticket, and API-key flows work with production session and proxy settings.
- The web server cannot execute content uploaded under either public media path.
- Both dependency lockfiles are deployed with the matching manifests and composer install --no-dev --prefer-dist --optimize-autoloader plus npm ci && npm run build complete successfully.
