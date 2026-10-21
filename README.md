# Coin

Source snapshot from `coin.zip` from the requested coin source folder (the original archive is retained locally).

## Excluded private and generated files

Private `.env` files, SQL dumps, runtime snapshots, logs, uploaded files, package dependencies, original bundled Markdown documentation and prebuilt archives are not included. Twenty embedded wallet private-key values in `api/database/seeders/ReceiveWordKeySeeder.php` were cleared in this upload copy. Do not use that seeder until it has been configured with your own appropriate test data.

Copy `api/.env.example` to `api/.env` and supply your local configuration. Copy `admin/.env.example` to `admin/.env.production` before building for production, or configure `.env.development` for local development. Obtain any database snapshot and original encryption keys through a private channel.

Install dependencies from the included lock files. Source was not executed or deployed during this repository import.
