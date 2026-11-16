# Hostinger frontend asset deployment

Alternova uses Laravel Vite and Tailwind CSS. Hostinger does not need Node.js or npm installed on the production hosting account.

## Normal deployment (git pull)

The frontend asset workflow builds CSS and JavaScript in GitHub Actions whenever frontend source or build configuration changes reach `main`.

After a successful build, the workflow commits the generated `public/build/` directory back to `main`. The directory is force-added because Laravel's default `.gitignore` excludes it. This means the existing Hostinger deployment procedure remains unchanged:

```bash
git pull origin main
```

The pull receives both the Laravel source changes and the matching compiled frontend assets. No manual artifact download, FTP configuration, Node.js installation, or separate asset upload is required.

**Important:** After merging a change that affects frontend assets, wait for the **Hostinger frontend assets** GitHub Actions workflow to finish successfully before running `git pull origin main`. The workflow creates a follow-up commit containing the built files. Pull again if you had already pulled before the build completed.

## What CI verifies

1. Installs exact frontend dependencies from `package-lock.json` with `npm ci` on GitHub Actions.
2. Runs `npm run test:js`.
3. Removes the development-server marker from the CI workspace.
4. Runs `npm run build`.
5. Verifies `public/build/manifest.json` and all referenced output files.
6. Commits the compiled `public/build/` output to `main`.
7. Publishes a 14-day artifact as a recovery option.

The workflow does not deploy PHP source directly to Hostinger, alter the database, touch `.env`, or modify user uploads/storage. Your normal `git pull origin main` remains the production source deployment mechanism.

## Production checks

After pulling, verify that:

- `public/build/manifest.json` exists on the server.
- CSS and JavaScript files referenced by the manifest exist under `public/build/assets/`.
- `public/hot` is absent from the production public directory.
- The homepage loads its CSS and JavaScript without 404 errors.

If the repository is already checked out on Hostinger, no hosting credentials or additional deployment integration are required.
