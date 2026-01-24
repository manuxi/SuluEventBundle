## Teaser Provider & Content Resolution

### Custom Entity Teasers
Um eigene Entitäten (z.B. Events) in der `teaser_selection` verfügbar zu machen, wurde das `Sulu\Bundle\AdminBundle\Teaser\Provider\TeaserProviderInterface` implementiert.
Entitäten werden manuell auf `Teaser`-Objekte gemappt.

### Integer IDs in Teaser Selection
Sulu erwartet in der `teaser_selection` standardmäßig String-IDs (UUIDs). Events nutzen jedoch Integer-IDs. Dies führt normalerweise zu Fehlern im Property Resolver.

**Lösung:**
Nutzen einen globalen Decorators: `App\Content\PropertyResolver\TeaserSelectionPropertyResolverDecorator`.
- **Funktion:** Er fängt den Resolver-Prozess ab, prüft auf Integer-IDs in den Items und castet diese hart nach `string`, bevor Sulu sie weiterverarbeitet.
- **Registrierung:** Er ist in der `services.yaml` mit dem Tag `sulu_content.property_resolver` und `priority: 100` registriert, um vor dem Standard-Resolver zu greifen.
- **Scope:** Diese Lösung gilt global für alle Bundles der App.

Eine Implementierung des Decorator befindet sich hier und kann als Vorlage dienen: `src/Content/PropertyResolver/TeaserSelectionPropertyResolverDecorator.php`.
Nachdem der Namespace angepasst und der Service in der `services.yaml` (Vorlage: `src/Resources/config/services-property-resolver-decorator.yaml`) registriert wurde, funktionieren die Event-Teaser.