# Documentation for Simple REST API Extension

This directory contains the documentation for the Simple REST API TYPO3 extension, written in reStructuredText (RST) format and built with Sphinx.

## Viewing Documentation

### Using DDEV (Recommended)

The easiest way to view the documentation during development is to use the DDEV documentation service:

1. **Start DDEV (if not already running)**
   ```bash
   ddev start
   ```

2. **Access the documentation**

   The documentation will be automatically built and served at:
   - HTTP: `http://simple-rest-api.ddev.site:9998`
   - HTTPS: `https://simple-rest-api.ddev.site:9999`

   The documentation will auto-rebuild when you make changes to any `.rst` files.

3. **Check container logs (if needed)**
   ```bash
   ddev logs -s docs
   ```

### Manual Building with Sphinx

If you want to build the documentation manually:

1. **Install Sphinx**
   ```bash
   pip install sphinx sphinx-rtd-theme
   ```

2. **Build HTML documentation**
   ```bash
   cd Documentation
   sphinx-build -b html . _build
   ```

3. **View the documentation**

   Open `_build/index.html` in your browser.

### Using Docker Directly

```bash
docker run --rm -v $(pwd)/Documentation:/docs python:3.11-slim sh -c \
  "pip install sphinx sphinx-rtd-theme && \
   cd /docs && \
   sphinx-build -b html . _build"
```

## Documentation Structure

```
Documentation/
├── Index.rst              # Main entry point
├── Includes.rst.txt       # Common definitions
├── Settings.cfg           # TYPO3-specific settings
├── conf.py               # Sphinx configuration
├── Introduction/         # What the extension does
├── Installation/         # Installation instructions
├── Configuration/        # Setup and configuration
├── Usage/               # User guide with examples
├── Developer/           # Developer documentation
├── KnownProblems/       # Troubleshooting
├── Changelog/           # Version history
└── Sitemap.rst          # Documentation sitemap
```

## Writing Documentation

### RST Syntax Quick Reference

- **Headings**: Use `=` under text for title, `-` for sections, `~` for subsections
- **Code blocks**: Use `.. code-block:: php` or `.. code-block:: bash`
- **Links**: `:ref:`label`` for internal links
- **Bold**: `**bold text**`
- **Italic**: `*italic text*`
- **Inline code**: `` `code` ``

### Adding New Pages

1. Create a new `.rst` file in the appropriate directory
2. Add it to the `toctree` in the parent `Index.rst` file
3. The documentation will auto-rebuild (if using DDEV)

### Code Examples

Always use proper syntax highlighting:

```rst
.. code-block:: php

   #[AsApiEndpoint(method: 'GET', path: '/hello', version: '1')]
   public function hello(): ResponseInterface
   {
       return new JsonResponse(['message' => 'Hello!']);
   }
```

## TYPO3 Documentation Standards

This documentation follows TYPO3 documentation standards:
- https://docs.typo3.org/m/typo3/docs-how-to-document/main/en-us/

## Publishing

The built documentation can be:
- Published to the TYPO3 Extension Repository (TER)
- Hosted on Read the Docs
- Included in the extension package
- Deployed to GitLab Pages or similar

## Troubleshooting

### Documentation not updating

If you make changes but don't see them:

1. Clear the build cache:
   ```bash
   ddev restart
   ```

2. Check for RST syntax errors in the logs:
   ```bash
   ddev logs -s docs
   ```

### Port conflicts

If port 9998 or 9999 is already in use, edit `.ddev/docker-compose.docs.yaml` and change the port numbers.

### Container won't start

Check container status:
```bash
ddev describe
docker ps -a | grep docs
```

Rebuild the container:
```bash
ddev restart
```

## Resources

- Sphinx documentation: https://www.sphinx-doc.org/
- RST primer: https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html
- TYPO3 docs: https://docs.typo3.org/
- Read the Docs theme: https://sphinx-rtd-theme.readthedocs.io/
