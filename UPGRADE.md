Upgrade Instructions
====================

## To v2.3.16 with Gedmo soft deleteable & folder tree

When upgrading from a previous version, make sure you update the table structure (```app/console doctrine:schema:update --force```
or ```app/console doctrine:migrations:diff && app/console doctrine:migrations:migrate```).

Afterwards run
- ```app/console kuma:media:migrate-soft-deletes``` to migrate the soft deletes
- ```app/console kuma:media:rebuild-folder-tree``` to initialize the folder tree

The media section should now be much faster then before (this will especially be noticeable if you have lots of media
folders).
