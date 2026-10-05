<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrediHub - Offline</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white font-sans">
    <div class="text-center max-w-md p-8 bg-gray-800/50 rounded-2xl border border-gray-700/50 shadow-xl">
        <div class="text-5xl mb-4">📡</div>
        <h1 class="text-2xl font-bold mb-2">Sem Conexão</h1>
        <p class="text-gray-300 mb-6">
            Parece que você está offline. Verifique sua conexão com a internet e tente novamente.
        </p>
        <button onclick="window.location.reload()" class="px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/30 rounded-2xl transition">
            Tentar Novamente
        </button>
        <div class="text-sm text-gray-400 mt-4" id="connectionStatus">
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