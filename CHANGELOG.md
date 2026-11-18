# Changelog

## Unreleased

### Added

- Dashboard tiles for [manuxi/sulu-dashboard-bundle](https://github.com/manuxi/SuluDashboardBundle) (^1.1): the next five published events with the number of unpublished ones, and locations without coordinates or a complete address. The dashboard is only suggested; the tile services are loaded only when its interface is available. Rights as in the event admin (`sulu.events.events`, view). Texts in German and English. See `docs/dashboard.en.md`.
- PHPStan (level 6) for the dashboard classes and their tests; `manuxi/sulu-dashboard-bundle` and `phpstan/phpstan` as dev dependencies.
