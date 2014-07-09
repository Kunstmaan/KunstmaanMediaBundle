# Changelog

### dev (2014-07-09)

* Updated/created timestamps were refactored to use Gedmo Timestampable
* Soft deletes were refactored to use Gedmo Softdeleteable
* The Folder::getMedia method signature has changed, the includeDeleted flag has been removed
* The Folder::getChildren method signature has changed, the includeDeleted flag has been removed
* Folder::setDeleted and Media::setDeleted are deprecated (and no longer used).
* If you use queries that use "deleted = false" (or 0) in the where clause you will have to modify these to use
"deleted_at IS NULL".
* A command 'kuma:media:migrate-soft-deletes' was added to migrate the deleted folders/media from the old to the new
structure.
