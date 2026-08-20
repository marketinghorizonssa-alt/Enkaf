# 2026-08-20 production asset incident

Observed on enkaf.sa immediately after the luxury redesign deployment: the page could load over HTTP but appeared unstyled and image treatments were missing; Chrome showed Not secure.

Root cause: the application sent `Content-Security-Policy: ...; upgrade-insecure-requests` even while the visitor was on HTTP and the domain did not yet have working HTTPS. Browsers therefore upgraded same-origin CSS and image subrequests to HTTPS, which failed, leaving bare HTML and oversized inline SVGs.

Response: keep the existing production backup and recorded GitHub release; change the CSP so `upgrade-insecure-requests` is added only when the current request is already HTTPS. Do not force an HTTP-to-HTTPS redirect until the certificate is verified. After the hotfix, verify CSS/photo assets over the active scheme, then complete SSL and enable the preferred HTTPS redirect.
