Upgrade Instructions
====================

UPGRADE FROM <=2.3.12 TO >2.3.12
================================

A new field to store the original filename was added to the Media table, so you will have to update the table structure
when upgrading from a version prior to 2.3.13.

After updating the MediaBundle you should run ```app/console doctrine:schema:update --force``` or
```app/console doctrine:migrations:diff && app/console doctrine:migrations:migrate```.

Then you can use ```app/console kuma:media:migrate``` to initialize the original filename field for already
uploaded media (it will just copy the contents of name field into the original_name field, so you could also just
update this using a simple SQL query).

If you upgrade and want to create PDF previews for PDF files that have already been uploaded, you can run
the ```app/console kuma:media:create-pdf-previews``` command.
