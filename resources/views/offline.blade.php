<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CondoAI - Offline</title>
    <meta name="theme-color" content="#1e293b">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f1f5f9;
        }

        .offline-container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 1rem;
            border: 2px solid rgba(6, 182, 212, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .offline-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #06b6d4, #ec4899);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #06b6d4, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #cbd5e1;
        }

        .retry-button {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .retry-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
        }

        .retry-button:active {
            transform: translateY(0);
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #ef4444;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 50% {
                opacity: 1;
            }
            51%, 100% {
                opacity: 0.3;
            }
        }

        .connection-status {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            📶
        </div>
        
        <div class="connection-status">
            <span class="status-indicator"></span>
            Sem conexão com a internet
        </div>

        <h1>Você está offline</h1>
        
        <p>
            Parece que você perdeu a conexão com a internet. 
            Algumas funcionalidades do CondoAI podem não estar disponíveis no momento.
        </p>
        
        <p>
            Verifique sua conexão e tente novamente.
        </p>

        <button class="retry-button" onclick="window.location.reload()">
            Tentar Novamente
        </button>
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

        // Atualizar status da conexão
        function updateConnectionStatus() {
            const statusIndicator = document.querySelector('.status-indicator');
            const statusText = document.querySelector('.connection-status');
            
            if (navigator.onLine) {
                statusIndicator.style.background = '#10b981';
                statusText.textContent = 'Conexão restaurada! Recarregando...';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                statusIndicator.style.background = '#ef4444';
                statusText.innerHTML = '<span class="status-indicator"></span>Sem conexão com a internet';
            }
        }

        // Verificar status inicial
        updateConnectionStatus();

        // Atualizar quando o status da conexão mudar
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
    </script>
</body>
</html> 