Upgrade Instructions
====================

UPGRADE FROM <=2.3.12 TO >2.3.12
================================

Some new fields have been added to the Media table, so you will have to update the table structure when upgrading from
a version prior to 2.3.13.

After updating the MediaBundle you should run ```app/console doctrine:schema:update --force``` or
```app/console doctrine:migrations:diff && app/console doctrine:migrations:migrate```.

Then you can use ```app/console kuma:media:migrate``` to initialize the new fields, not doing this *will* cause issues!
