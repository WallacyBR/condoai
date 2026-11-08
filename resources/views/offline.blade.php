<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CondoAI - Offline</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .offline-container {
            text-align: center;
            max-width: 400px;
            padding: 2rem;
        }
        
        .offline-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .offline-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .offline-message {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .retry-button {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .retry-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .connection-status {
            margin-top: 1rem;
            font-size: 0.875rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        <h1 class="offline-title">Sem Conexão</h1>
        <p class="offline-message">
            Parece que você está offline. Verifique sua conexão com a internet e tente novamente.
        </p>
        <button class="retry-button" onclick="window.location.reload()">
            Tentar Novamente
        </button>
        <div class="connection-status" id="connectionStatus">
            Verificando conexão...
        </div>
    </div>

    <script>
        // Verificar status da conexão
        function checkConnection() {
            const statusElement = document.getElementById('connectionStatus');
            
            if (navigator.onLine) {
                statusElement.textContent = 'Conexão restaurada! Redirecionando...';
                setTimeout(() => {
                    window.location.href = '/';
                }, 2000);
            } else {
                statusElement.textContent = 'Ainda offline';
            }
        }
        
        // Event listeners para mudanças de conectividade
        window.addEventListener('online', checkConnection);
        window.addEventListener('offline', checkConnection);
        
        // Verificar inicialmente
        checkConnection();
        
        // Verificar periodicamente
        setInterval(checkConnection, 5000);
    </script>
</body>
</html> 