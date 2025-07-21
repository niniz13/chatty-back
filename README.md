# create entity

php bin/console make:entity

# create migrations

php bin/console make:migration

# run migrations

php bin/console doctrine:migrations:migrate

# rollback migration
(supprimer CREATE SCHEMA public dans le fichier de migration)

php bin/console doctrine:migrations:migrate prev