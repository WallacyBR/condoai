<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CondoAI - Offline</title>
    <meta name="theme-color" content="#1e293b">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .offline-container {
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        
        .offline-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .offline-icon svg {
            width: 40px;
            height: 40px;
            color: #fbbf24;
        }
        
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .retry-button {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }
        
        .retry-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
        
        .retry-button:active {
            transform: translateY(0);
        }
        
        .features {
            margin-top: 2rem;
            text-align: left;
        }
        
        .features h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #fbbf24;
        }
        
        .feature-list {
            list-style: none;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
        }
        
        .feature-list li::before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        
        <h1>Você está offline</h1>
        <p>Parece que você perdeu a conexão com a internet. Algumas funcionalidades podem estar indisponíveis.</p>
        
        <button class="retry-button" onclick="window.location.reload()">
            Tentar novamente
        </button>
        
        <div class="features">
            <h3>Funcionalidades disponíveis offline:</h3>
            <ul class="feature-list">
                <li>Visualizar dados já carregados</li>
                <li>Navegar entre páginas visitadas</li>
                <li>Acessar informações em cache</li>
                <li>Ver histórico de atividades</li>
            </ul>
        </div>
    </div>

    <script>
        // Verificar conectividade periodicamente
        function checkConnection() {
            if (navigator.onLine) {
                window.location.reload();
            }
        }
        
        // Verificar a cada 5 segundos
        setInterval(checkConnection, 5000);
        
        // Verificar quando a conexão for restaurada
        window.addEventListener('online', () => {
            window.location.reload();
        });
    </script>
</body>
</html> 