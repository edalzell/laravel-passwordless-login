# Changelog

All notable changes to Laravel Passwordless Login will be documented in this file.

## v2.2.4 - 2026-11-03

### 🔒 Security

- High: a request body could override the signed login identity, allowing account takeover ([GHSA-3c8g-w468-p4pm](https://github.com/edalzell/laravel-passwordless-login/security/advisories/GHSA-3c8g-w468-p4pm))
- Moderate: open redirect via unsigned `redirect_to` ([GHSA-4wf6-gqv9-mw65](https://github.com/edalzell/laravel-passwordless-login/security/advisories/GHSA-4wf6-gqv9-mw65))

Thanks to [@ValCanBuild](https://github.com/ValCanBuild) for reporting these. All users should upgrade.

### 🐛 Fixed

- Only trust signed login values in [`c1e3b8f`](https://github.com/edalzell/laravel-passwordless-login/commit/c1e3b8f5c4bdfca8a13fe39e2c04a3ddf21af550)

### 🧰 Maintenance

- Use shared release-drafter config [@edalzell](https://github.com/edalzell) (#157)

## v2.2.3 - 2026-09-24

### 🐛 Fixed

- Treat unresolvable user_type as an invalid link [@ValCanBuild](https://github.com/ValCanBuild) (#154)

### 🧰 Maintenance

- Run release drafter on pull_request_target [@edalzell](https://github.com/edalzell) (#155)
- Freeze time before generating test login URL [@edalzell](https://github.com/edalzell) (#156)
- Pin pr-labeler action and scope its permissions [@edalzell](https://github.com/edalzell) (#152)

## v2.2.2 - 2026-09-14

### 🐛 Fixed

- Request::get() has been deprecated, use input() [@erikverbeek](https://github.com/erikverbeek) (#150)

### 🧰 Maintenance

- Convert release flow to PR-gated reusable workflows [@edalzell](https://github.com/edalzell) (#151)

## v2.2.1 - 2026-07-26

### 🐛 Fixed

- Ensure soft-deleted users are not triggering TypeErrors [@spekulatius](https://github.com/spekulatius) (#146)

### 🧰 Maintenance

- Small tidy from PR [@edalzell](https://github.com/edalzell) (#147)
- Use Pest [@edalzell](https://github.com/edalzell) (#138)

## v2.2.0 - 2026-07-13

### 🚀 New

- Allow configuring a separate cache store for link markers [@edalzell](https://github.com/edalzell) (#144)
- Make cache-marker requirement opt-in for link validity [@edalzell](https://github.com/edalzell) (#143)
- Derive guard name from user_type when retrieving the user [@edalzell](https://github.com/edalzell) (#134)
- Login route can be `post` [@edalzell](https://github.com/edalzell) (#133)
- Dispatch events [@edalzell](https://github.com/edalzell) (#131)

### 🐛 Fixed

- Fix link invalidation [@edalzell](https://github.com/edalzell) (#142)

### 🧰 Maintenance

- Freeze time in test [@edalzell](https://github.com/edalzell) (#132)
- General code tidying [@edalzell](https://github.com/edalzell) (#139)
- camelCase properties [@edalzell](https://github.com/edalzell) (#136)

## v2.1.0 - 2026-07-03

### 🚀 New

- Invalidate links [@edalzell](https://github.com/edalzell) (#129)

### 🧰 Maintenance

- Update GitHub Action Versions [@edalzell](https://github.com/edalzell) (#128)
- Bump shivammathur/setup-php from 2.37.0 to 2.37.1 in /.github/workflows [@[dependabot[bot]](https://github.com/apps/dependabot)](https://github.com/[dependabot[bot]](https://github.com/apps/dependabot)) (#126)

## v2.0.1 - 2026-03-31

### 🐛 Fixed

- Update the default user model namespace [@edalzell](https://github.com/edalzell) (#120)

### 🧰 Maintenance

- Update readme [@edalzell](https://github.com/edalzell) (#123)
- Format readme [@edalzell](https://github.com/edalzell) (#122)
- Fix styling [@edalzell](https://github.com/edalzell) (#121)
- No assets to build [@edalzell](https://github.com/edalzell) (#119)

## v2.0.0 - 2026-02-26

### 🚀 New

- Laravel 13.x Compatibility [@laravel-shift](https://github.com/laravel-shift) (#117)
- Drop support for Laravel 6, 7, 8, 9, 10 [@edalzell](https://github.com/edalzell) (#116)

#### 🐛 Fixed

- Ensure `login_route_expires` is an int [@edalzell](https://github.com/edalzell) (#118)
- Fix unit tests [@DavidGoodwin](https://github.com/DavidGoodwin) (#111)

#### 🧰 Maintenance

- Add useful GH actions  [@edalzell](https://github.com/edalzell) (#115)
- Improve README [@szepeviktor](https://github.com/szepeviktor) (#86)
- Improve PasswordlessLogin trait [@szepeviktor](https://github.com/szepeviktor) (#90)
- Remove useless property from LoginUrl [@szepeviktor](https://github.com/szepeviktor) (#89)
- Improve PasswordlessLoginService [@szepeviktor](https://github.com/szepeviktor) (#87)

## v1.11 - 2026-03-03

### 🚀 New

- Adds Laravel 12 support [@laravel-shift](https://github.com/laravel-shift) (#110)

## v1.10 - 2024-05-16

### 🚀 New

- Adds Laravel 11 support [@laravel-shift](https://github.com/laravel-shift) (#102)
