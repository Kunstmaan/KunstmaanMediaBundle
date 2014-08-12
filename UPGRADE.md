Upgrade Instructions
====================

## To v2.3.X with extra fields, Gedmo soft deleteable & folder tree

When upgrading from a previous version, make sure you update the table structure (
```app/console doctrine:schema:update --force```
or ```app/console doctrine:migrations:diff && app/console doctrine:migrations:migrate```).

A new field to store the original filename was added to the Media table, so you will have to update the table structure
when upgrading from a version prior to 2.3.X.

You can use ```app/console kuma:media:migrate-name``` to initialize the original filename field for already
uploaded media (it will just copy the contents of name field into the original_name field, so you could also just
update this using a simple SQL query).

Also make sure that the gedmo soft delete and timestampable behavior is enabled by checking your app/config.yml file.
It should contain :
https://github.com/Kunstmaan/KunstmaanBundlesStandardEdition/pull/71/files

Afterwards run
- ```app/console kuma:media:migrate-soft-deletes``` to migrate the soft deletes
- ```app/console kuma:media:rebuild-folder-tree``` to initialize the folder tree

The media section should now be much faster then before (this will especially be noticeable if you have lots of media
folders).

If you upgrade and want to create PDF preview images for PDF files that have already been uploaded, you can run
the ```app/console kuma:media:create-pdf-previews``` command.
