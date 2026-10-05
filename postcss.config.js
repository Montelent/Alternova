/**
 * PostCSS is optional with Tailwind v4 + @tailwindcss/vite.
 * Kept as a no-op so older tooling that expects the file does not break.
 * Do NOT register `tailwindcss` here — that is the v3 API and causes build errors on v4.
 */
export default {
    plugins: {},
};
