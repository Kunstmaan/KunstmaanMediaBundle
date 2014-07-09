Upgrade Instructions
====================

## To v2.3.16 with Gedmo soft deleteable

When upgrading from a previous version, make sure you update the table structure (```app/console doctrine:schema:update --force```
or ```app/console doctrine:migrations:diff && app/console doctrine:migrations:migrate```). And afterwards run
```app/console kuma:node:migrate-soft-deletes```. All deleted nodes will get the current date as deleted_at timestamp.
