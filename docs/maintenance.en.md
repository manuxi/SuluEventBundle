# Maintenance

## Clean up the stored image data

The image of an event is stored in the `image_id` column. Older versions additionally wrote the whole media object of the form (url, thumbnails, mime type) into the content data of the event on every save. Those copies are redundant and can contain outdated thumbnail urls, for example after an image format changed.

The command reduces them to `{"id": n}`, in drafts and in the live version, in every locale. The id is taken from the `image_id` column. Nothing else of the event is changed.

```bash
# only list what would be changed
bin/console sulu:events:clean-image-data --dry-run

# do it
bin/console sulu:events:clean-image-data
```

Run it once after updating. It is safe to run again; rows that are already clean are skipped. Rows that cannot be processed are listed with their error and the exit code is not 0, the other rows are still processed.

New saves no longer write the image into the content data. This needs a Sulu version that knows the tag `sulu_content.skip_template_data_mapper`; on older 3.0 versions the image object is still stored on save, and the command can be run again at any time.
