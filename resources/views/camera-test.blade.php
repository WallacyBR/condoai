<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste da Câmera - CondoAI</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div id="app">
        <div class="container mx-auto py-8">
            <h1 class="text-3xl font-bold text-center mb-8">Teste da Câmera</h1>
            <camera-test></camera-test>
        </div>
    </div>

    <script type="module">
        import { createApp } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'
        import CameraTest from '/js/components/CameraTest.vue'

        const app = createApp({
            components: {
                CameraTest
            }
        })

        app.mount('#app')
    </script>
</body>
</html> 