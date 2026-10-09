# Wartung

## Gespeicherte Bilddaten bereinigen

Das Bild eines Events steht in der Spalte `image_id`. Ältere Versionen haben bei jedem Speichern zusätzlich das ganze Medienobjekt des Formulars (URL, Thumbnails, Mime-Type) in die Inhaltsdaten des Events geschrieben. Diese Kopien sind überflüssig und können veraltete Thumbnail-URLs enthalten, zum Beispiel nach einer Änderung der Bildformate.

Der Command reduziert sie auf `{"id": n}`, im Entwurf und in der Live-Version, in jeder Sprache. Die ID kommt aus der Spalte `image_id`. Sonst wird am Event nichts verändert.

```bash
# nur anzeigen, was geändert würde
bin/console sulu:events:clean-image-data --dry-run

# ausführen
bin/console sulu:events:clean-image-data
```

Nach dem Update einmal ausführen. Ein erneuter Lauf ist unbedenklich; bereits saubere Zeilen werden übersprungen. Zeilen, die sich nicht verarbeiten lassen, werden mit ihrem Fehler aufgelistet und der Exit-Code ist dann nicht 0, die übrigen Zeilen werden trotzdem bearbeitet.

Beim Speichern wird das Bild nicht mehr in die Inhaltsdaten geschrieben. Das setzt eine Sulu-Version voraus, die den Tag `sulu_content.skip_template_data_mapper` kennt; bei älteren 3.0-Versionen wird das Bildobjekt weiterhin gespeichert, der Command lässt sich jederzeit erneut ausführen.
