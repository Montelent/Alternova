# Hostinger frontend asset deployment

Alternova is a Laravel application using Vite and Tailwind CSS. Hostinger PHP/shared hosting does not need Node.js installed on the production account. Node.js is used only by the GitHub Actions build runner.

## What the workflow does

The `.github/workflows/hostinger-assets.yml` workflow:

1. Installs the exact frontend dependency versions from `package-lock.json` with `npm ci` on GitHub Actions.
2. Runs the JavaScript behavior tests.
3. builds production CSS and JavaScript with `npm run build`.
4. Verifies that `public/build/manifest.json` exists and every referenced output file is present.
5. Publishes `public/build/` as the `alternova-hostinger-public-build` workflow artifact.
6. If the Hostinger secrets below are configured, deploys only `public/build/` over explicit FTPS and removes a stale `public/hot` marker from the public document root.

The workflow never uploads the Laravel source tree, `.env`, storage, user uploads, or database files. It does not delete the rest of the remote public directory.

## Configure automatic Hostinger asset upload

In GitHub, open **Montelent/Alternova → Settings → Secrets and variables → Actions → New repository secret** and add:

- `HOSTINGER_FTP_SERVER`: the FTP hostname shown in Hostinger hPanel.
- `HOSTINGER_FTP_USERNAME`: the FTP account username.
- `HOSTINGER_FTP_PASSWORD`: that FTP account's password.
- `HOSTINGER_PUBLIC_DIR`: the FTP path to the directory that is the live Laravel public web root. Do not include `/build` at the end.

For example, if the FTP account's public web root is `public_html`, set `HOSTINGER_PUBLIC_DIR` to `public_html`. If Hostinger maps the domain to a different directory, use the exact directory shown in hPanel. Do not guess this path.

Use an FTP account restricted to the website directory where possible. The workflow uses explicit FTPS with certificate verification; it does not fall back to unencrypted FTP.

Once the four secrets are set, each qualifying push to `main` builds and deploys the compiled assets automatically. Until then, pull requests still build, test, and publish a downloadable artifact, but no production upload occurs.

## Hostinger source deployment remains separate

This workflow deploys frontend build output only. Keep the existing Hostinger Git deployment or other PHP release process responsible for deploying Laravel source, Composer dependencies, migrations, and application files. The public document root must serve the uploaded `build/` directory at `/build/`.

If deploying manually, download the `alternova-hostinger-public-build` artifact from the successful GitHub Actions run and upload its contents to the live public web root's `build/` directory. Do not upload the enclosing artifact folder as an extra nested directory.

## Post-deployment verification

- Confirm `https://YOUR-DOMAIN/build/manifest.json` returns JSON (not a 404 or HTML error page).
- Open the CSS and JS paths referenced by the manifest and confirm they return successfully.
- Confirm `public/hot` is absent from the live public web root.
- Reload the homepage with browser cache disabled. The page should load the compiled stylesheet and JavaScript.
- If the deployment path is wrong, correct `HOSTINGER_PUBLIC_DIR` in GitHub Secrets and rerun the workflow. Do not change Laravel asset URLs to hardcoded domains.
