### Prerequisites
- [ddev](https://ddev.com/get-started/)

### Setup
- `ddev config` # use defaults
- `ddev start`
- `ddev exec composer install`
- `ddev exec drush sqlc < dumps/default.sql`
- `ddev exec drush cim`

### Start
- `ddev start`

### Admin access
- `ddev exec drush user:login`
