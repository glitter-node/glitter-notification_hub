# Notification Hub documentation

- [Architecture](architecture.md)
- [Testing and safety boundary](testing.md)
- [Administrator API](api/README.md)
- [Administrator Layout design](design/admin-layouts.md)

Backend translations live in `src/lang/{locale}` and are loaded by the module service
provider. Layout translations live separately in `resources/lang/{locale}.json` and are
wrapped with the module identifier by the frontend language loader.
