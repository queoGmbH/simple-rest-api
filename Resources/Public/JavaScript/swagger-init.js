(function() {
    function initSwagger() {
        const container = document.getElementById('swagger-ui');
        if (!container) {
            console.error('Swagger UI container not found');
            return;
        }

        const openApiUrl = container.getAttribute('data-openapi-url');
        if (!openApiUrl) {
            console.error('OpenAPI URL not specified');
            return;
        }

        if (typeof SwaggerUIBundle === 'undefined' || typeof SwaggerUIStandalonePreset === 'undefined') {
            console.log('Swagger UI not yet loaded, retrying...');
            setTimeout(initSwagger, 100);
            return;
        }

        try {
            const ui = SwaggerUIBundle({
                url: openApiUrl,
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "list",
                filter: true,
                showRequestHeaders: true,
                tryItOutEnabled: true
            });

            window.ui = ui;
            console.log('Swagger UI initialized successfully');
        } catch (error) {
            console.error('Failed to initialize Swagger UI:', error);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSwagger);
    } else {
        initSwagger();
    }
})();
