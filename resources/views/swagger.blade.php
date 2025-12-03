<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI - Microservicio Helpdesk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        #swagger-ui {
            min-height: 100vh;
        }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    window.onload = () => {
        SwaggerUIBundle({
            url: "{{ app()->environment('production') ? secure_asset('openapi.json') : asset('openapi.json') }}",
            dom_id: '#swagger-ui',
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIBundle.SwaggerUIStandalonePreset
            ],
            layout: "BaseLayout"
        });
    };
</script>
</body>
</html>
